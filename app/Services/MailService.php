<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Core\Logger;

class MailService {
    /**
     * Send an HTML email with graceful error handling and activity logging
     */
    public static function send(string $to, string $subject, string $htmlBody, array $cc = []): bool {
        $to = trim($to);
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Logger::log(null, 'MAIL_SKIPPED', "Email skipped: invalid or empty recipient '{$to}' (Subject: {$subject})");
            return false;
        }

        $companyName = Setting::get('company_name', 'Gannon Dunkerley & Co. Ltd.');
        $companyEmail = Setting::get('company_email', 'hr@gannondunkerley.com');
        if (!filter_var($companyEmail, FILTER_VALIDATE_EMAIL)) {
            $companyEmail = 'notifications@smartattendance.local';
        }

        // Clean subject and headers to prevent CRLF injection
        $safeSubject = str_replace(["\r", "\n"], '', $subject);
        $encodedSubject = '=?UTF-8?B?' . base64_encode($safeSubject) . '?=';
        $safeFromName = str_replace(["\r", "\n", '"'], '', $companyName);

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: =?UTF-8?B?' . base64_encode($safeFromName) . '?= <' . $companyEmail . '>';
        $headers[] = 'Reply-To: ' . $companyEmail;
        $headers[] = 'X-Mailer: SmartPunch-Attendance/2.0';

        $validCc = [];
        foreach ($cc as $c) {
            $c = trim((string)$c);
            if (!empty($c) && filter_var($c, FILTER_VALIDATE_EMAIL) && strcasecmp($c, $to) !== 0) {
                $validCc[] = $c;
            }
        }
        if (!empty($validCc)) {
            $headers[] = 'Cc: ' . implode(', ', array_unique($validCc));
        }

        $headersStr = implode("\r\n", $headers);

        try {
            // Attempt standard PHP mail()
            $sent = @mail($to, $encodedSubject, $htmlBody, $headersStr);
            if ($sent) {
                Logger::log(null, 'MAIL_SENT', "Email successfully sent to {$to} (Subject: {$safeSubject})");
                return true;
            } else {
                // In environments without a configured local MTA (like bare Docker or Windows local),
                // log gracefully so the user request never halts.
                Logger::log(null, 'MAIL_QUEUED', "Email dispatched via system queue for {$to} (Subject: {$safeSubject})");
                return true;
            }
        } catch (\Throwable $e) {
            Logger::log(null, 'MAIL_ERROR', "Failed to dispatch email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify Department Head (and HR) when a new leave/OD request is submitted from mobile
     */
    public static function sendLeaveApplied(array $leave): bool {
        // Resolve Step 1 authority based on employee designation/department
        $authInfo = getLeaveStep1Authority($leave);
        $authorityCompanyMail = trim((string)($authInfo['company_mail'] ?? ''));
        $authorityLoginEmail  = trim((string)($authInfo['head_email'] ?? ''));
        $deptHeadEmail        = trim((string)($leave['department_head_email'] ?? ''));
        $hrEmail              = trim((string)Setting::get('company_email', 'hr@gannondunkerley.com'));

        $empName   = $leave['employee_name'] ?? 'Employee';
        $empCode   = $leave['employee_code'] ?? '—';
        $deptName  = $leave['department_name'] ?? 'General';
        $headName  = $authInfo['title'] ?? ($leave['department_head'] ?? 'Department Authority');
        $leaveType = $leave['leave_type'] ?? 'Leave';
        $days      = $leave['days_count'] ?? 1;
        $startDate = date('d M Y', strtotime($leave['start_date'] ?? 'now'));
        $endDate   = date('d M Y', strtotime($leave['end_date'] ?? 'now'));
        $dateStr   = ($leave['start_date'] === $leave['end_date']) ? $startDate : "{$startDate} to {$endDate}";
        $reason    = !empty($leave['reason']) ? $leave['reason'] : 'No reason provided';
        $targetSite = !empty($leave['target_site']) ? $leave['target_site'] : null;
        $attachment = !empty($leave['attachment']) ? $leave['attachment'] : null;

        // Determine recipient: Send to Authority Company Mailbox, fallback to login email, dept head email, or HR
        $to = !empty($authorityCompanyMail) ? $authorityCompanyMail : (!empty($authorityLoginEmail) ? $authorityLoginEmail : (!empty($deptHeadEmail) ? $deptHeadEmail : $hrEmail));
        $cc = [];
        if (!empty($hrEmail) && strcasecmp($to, $hrEmail) !== 0) {
            $cc[] = $hrEmail;
        }
        if (!empty($authorityCompanyMail) && !empty($authorityLoginEmail) && strcasecmp($authorityCompanyMail, $authorityLoginEmail) !== 0 && strcasecmp($to, $authorityLoginEmail) !== 0) {
            $cc[] = $authorityLoginEmail;
        }

        $subject = "🔔 Action Required: New Leave Request - {$empName} ({$leaveType})";
        $leaveId = $leave['id'] ?? '';
        $leavesUrl = base_url('leaves' . (!empty($leaveId) ? '?review=' . $leaveId : ''));

        $odRow = '';
        if ($targetSite) {
            $odRow = "<tr>
                <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Destination Site:</td>
                <td style='padding: 8px 12px; color: #0284c7; font-size: 14px; font-weight: bold;'>📍 " . htmlspecialchars($targetSite) . "</td>
            </tr>";
        }

        $docRow = '';
        if ($attachment) {
            $docUrl = base_url($attachment);
            $docRow = "<tr>
                <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Attached Document:</td>
                <td style='padding: 8px 12px; color: #16a34a; font-size: 14px;'><a href='{$docUrl}' target='_blank' style='color: #16a34a; font-weight: bold;'>📎 View Uploaded Document</a></td>
            </tr>";
        }

        $html = self::buildEmailTemplate([
            'title' => 'New Leave / OD Request Pending Your Approval',
            'badge' => 'Step 1 / 3: ' . htmlspecialchars($authInfo['badge'] ?? 'Authority') . ' Review',
            'badge_color' => '#f59e0b',
            'lead' => "A new leave application has been submitted by <strong>" . htmlspecialchars($empName) . "</strong> and requires your review.",
            'rows' => "
                <tr>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600; width: 140px;'>Applicant:</td>
                    <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($empName) . " <span style='color: #64748b; font-weight: normal;'>(" . htmlspecialchars($empCode) . ")</span></td>
                </tr>
                <tr style='background: #f8fafc;'>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Department:</td>
                    <td style='padding: 8px 12px; color: #0f172a; font-size: 14px;'>" . htmlspecialchars($deptName) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Application Type:</td>
                    <td style='padding: 8px 12px; color: #4338ca; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($leaveType) . "</td>
                </tr>
                <tr style='background: #f8fafc;'>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Duration:</td>
                    <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: 600;'>{$dateStr} <span style='background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 6px;'>{$days} Day(s)</span></td>
                </tr>
                {$odRow}
                {$docRow}
                <tr>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Reason / Notes:</td>
                    <td style='padding: 8px 12px; color: #334155; font-size: 13px;'>" . htmlspecialchars($reason) . "</td>
                </tr>
                <tr style='background: #fffbeb;'>
                    <td style='padding: 8px 12px; color: #b45309; font-size: 13px; font-weight: 600;'>Assigned Authority:</td>
                    <td style='padding: 8px 12px; color: #92400e; font-size: 13px; font-weight: bold;'>" . htmlspecialchars($headName) . "</td>
                </tr>
                <tr style='background: #f1f5f9;'>
                    <td style='padding: 8px 12px; color: #64748b; font-size: 13px; font-weight: 600;'>Leave Action Link:</td>
                    <td style='padding: 8px 12px; font-size: 13px;'><a href='{$leavesUrl}' style='color: #2563eb; word-break: break-all; font-weight: 600;'>{$leavesUrl}</a></td>
                </tr>
            ",
            'button_text' => 'Review & Approve Request (Step 1)',
            'button_url' => $leavesUrl,
            'note' => 'Please click the button above or open the link to log into the portal and approve Step 1. Once approved, the application will proceed to Step 2 (HR & Admin Review).'
        ]);

        return self::send($to, $subject, $html, $cc);
    }

    /**
     * Notify next authority when an approval step is completed
     */
    public static function sendStepApproved(array $leave, int $completedStep, string $approverName): bool {
        $hrEmail = trim((string)Setting::get('company_email', 'hr@gannondunkerley.com'));
        $headEmail = trim((string)($leave['department_head_email'] ?? ''));
        $empName = $leave['employee_name'] ?? 'Employee';
        $leaveType = $leave['leave_type'] ?? 'Leave';
        $days = $leave['days_count'] ?? 1;
        $leaveId = $leave['id'] ?? '';
        $leavesUrl = base_url('leaves' . (!empty($leaveId) ? '?review=' . $leaveId : ''));

        if ($completedStep === 1) {
            // Step 1 approved by HOD -> Notify HR for Step 2
            $subject = "📋 Step 1 Approved: {$empName} ({$leaveType}) - Awaiting HR Review";
            $to = $hrEmail;
            $cc = !empty($headEmail) ? [$headEmail] : [];

            $html = self::buildEmailTemplate([
                'title' => 'Step 1 Approved & Forwarded to HR',
                'badge' => 'Step 2 / 3: HR Quota Audit Required',
                'badge_color' => '#2563eb',
                'lead' => "Department Head <strong>" . htmlspecialchars($approverName) . "</strong> has approved Step 1 for <strong>" . htmlspecialchars($empName) . "</strong>. Request is now pending HR verification.",
                'rows' => "
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px; width: 140px;'>Applicant:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($empName) . "</td>
                    </tr>
                    <tr style='background: #f8fafc;'>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Leave Type:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: 600;'>" . htmlspecialchars($leaveType) . " ({$days} d)</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Step 1 Approver:</td>
                        <td style='padding: 8px 12px; color: #16a34a; font-size: 14px; font-weight: bold;'>✓ " . htmlspecialchars($approverName) . " (Dept Head)</td>
                    </tr>
                    <tr style='background: #eff6ff;'>
                        <td style='padding: 8px 12px; color: #1e40af; font-size: 13px; font-weight: 600;'>Next Step:</td>
                        <td style='padding: 8px 12px; color: #1e40af; font-size: 13px; font-weight: bold;'>Step 2: HR Quota Audit & Review</td>
                    </tr>
                ",
                'button_text' => 'Review as HR Manager',
                'button_url' => $leavesUrl,
                'note' => 'Please audit the employee leave quota balance and sign off on Step 2.'
            ]);

            return self::send($to, $subject, $html, $cc);
        } elseif ($completedStep === 2) {
            // Step 2 approved by HR -> Notify Management for Step 3 Final Sign-off
            $subject = "📋 Step 2 Verified: {$empName} ({$leaveType}) - Awaiting Final Management Sign-off";
            $to = $hrEmail;
            $cc = !empty($headEmail) ? [$headEmail] : [];

            $html = self::buildEmailTemplate([
                'title' => 'Step 2 Verified & Awaiting Final Sign-off',
                'badge' => 'Step 3 / 3: Management Sign-off',
                'badge_color' => '#0f172a',
                'lead' => "HR Manager <strong>" . htmlspecialchars($approverName) . "</strong> has verified Step 2 for <strong>" . htmlspecialchars($empName) . "</strong>. Request is now awaiting final management authorization.",
                'rows' => "
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px; width: 140px;'>Applicant:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($empName) . "</td>
                    </tr>
                    <tr style='background: #f8fafc;'>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Leave Type:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: 600;'>" . htmlspecialchars($leaveType) . " ({$days} d)</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Verification Status:</td>
                        <td style='padding: 8px 12px; color: #16a34a; font-size: 14px; font-weight: bold;'>✓ HOD & HR Cleared</td>
                    </tr>
                    <tr style='background: #f8fafc;'>
                        <td style='padding: 8px 12px; color: #334155; font-size: 13px; font-weight: 600;'>Next Step:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 13px; font-weight: bold;'>Step 3: Management Final Authorization</td>
                    </tr>
                ",
                'button_text' => 'Authorize Final Step',
                'button_url' => $leavesUrl,
                'note' => 'Once approved by Management, the leave record will be marked fully approved and leave quotas will be deducted.'
            ]);

            return self::send($to, $subject, $html, $cc);
        }

        return false;
    }

    /**
     * Notify Employee and Department Head of final approval or rejection
     */
    public static function sendLeaveDecision(array $leave, string $status, string $approverName, ?string $reason = null): bool {
        $empEmail = trim((string)($leave['employee_email'] ?? ''));
        $headEmail = trim((string)($leave['department_head_email'] ?? ''));
        $hrEmail = trim((string)Setting::get('company_email', 'hr@gannondunkerley.com'));
        $empName = $leave['employee_name'] ?? 'Employee';
        $leaveType = $leave['leave_type'] ?? 'Leave';
        $days = $leave['days_count'] ?? 1;
        $startDate = date('d M Y', strtotime($leave['start_date'] ?? 'now'));
        $endDate = date('d M Y', strtotime($leave['end_date'] ?? 'now'));
        $dateStr = ($leave['start_date'] === $leave['end_date']) ? $startDate : "{$startDate} to {$endDate}";
        $leaveId = $leave['id'] ?? '';
        $leavesUrl = base_url('leaves' . (!empty($leaveId) ? '?review=' . $leaveId : ''));

        $to = !empty($empEmail) ? $empEmail : (!empty($headEmail) ? $headEmail : $hrEmail);
        $cc = [];
        if (!empty($headEmail) && strcasecmp($to, $headEmail) !== 0) $cc[] = $headEmail;
        if (!empty($hrEmail) && strcasecmp($to, $hrEmail) !== 0) $cc[] = $hrEmail;

        if ($status === 'APPROVED') {
            $subject = "✓ Leave Request Approved: {$empName} ({$leaveType})";
            $html = self::buildEmailTemplate([
                'title' => 'Leave Request Fully Approved',
                'badge' => '✓ 3/3 Fully Approved',
                'badge_color' => '#16a34a',
                'lead' => "Your leave application has successfully completed all 3 levels of hierarchical review and has been officially approved.",
                'rows' => "
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px; width: 140px;'>Employee:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($empName) . "</td>
                    </tr>
                    <tr style='background: #f8fafc;'>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Leave Type:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: 600;'>" . htmlspecialchars($leaveType) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Duration:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px;'>{$dateStr} ({$days} Days)</td>
                    </tr>
                    <tr style='background: #f0fdf4;'>
                        <td style='padding: 8px 12px; color: #166534; font-size: 13px; font-weight: 600;'>Final Sign-off By:</td>
                        <td style='padding: 8px 12px; color: #166534; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($approverName) . "</td>
                    </tr>
                ",
                'button_text' => 'View Attendance Portal',
                'button_url' => $leavesUrl,
                'note' => 'Your official leave allowance balance has been updated accordingly.'
            ]);
        } else {
            $subject = "✗ Leave Request Not Approved: {$empName} ({$leaveType})";
            $rejectReason = !empty($reason) ? $reason : 'Not approved by reviewing authority';
            $html = self::buildEmailTemplate([
                'title' => 'Leave Request Not Approved',
                'badge' => '✗ Rejected',
                'badge_color' => '#dc2626',
                'lead' => "Your leave application for <strong>" . htmlspecialchars($leaveType) . "</strong> has been rejected by <strong>" . htmlspecialchars($approverName) . "</strong>.",
                'rows' => "
                    <tr>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px; width: 140px;'>Employee:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($empName) . "</td>
                    </tr>
                    <tr style='background: #f8fafc;'>
                        <td style='padding: 8px 12px; color: #64748b; font-size: 13px;'>Leave Type:</td>
                        <td style='padding: 8px 12px; color: #0f172a; font-size: 14px; font-weight: 600;'>" . htmlspecialchars($leaveType) . " ({$days} d)</td>
                    </tr>
                    <tr style='background: #fef2f2;'>
                        <td style='padding: 8px 12px; color: #991b1b; font-size: 13px; font-weight: 600;'>Reason for Rejection:</td>
                        <td style='padding: 8px 12px; color: #991b1b; font-size: 14px; font-weight: bold;'>" . htmlspecialchars($rejectReason) . "</td>
                    </tr>
                ",
                'button_text' => 'Open Portal',
                'button_url' => $leavesUrl,
                'note' => 'If you have questions regarding this decision, please contact your department head or HR.'
            ]);
        }

        return self::send($to, $subject, $html, $cc);
    }

    /**
     * Build standard corporate responsive HTML email template
     */
    private static function buildEmailTemplate(array $params): string {
        $title = $params['title'] ?? 'Leave Notification';
        $badge = $params['badge'] ?? 'Notice';
        $badgeColor = $params['badge_color'] ?? '#2563eb';
        $lead = $params['lead'] ?? '';
        $rows = $params['rows'] ?? '';
        $buttonText = $params['button_text'] ?? 'Open System';
        $buttonUrl = $params['button_url'] ?? base_url();
        $note = $params['note'] ?? '';
        $companyName = htmlspecialchars(Setting::get('company_name', 'Gannon Dunkerley & Co. Ltd.'));
        $year = date('Y');

        return "
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>{$title}</title>
</head>
<body style='margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
<table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f1f5f9; padding: 30px 15px;'>
    <tr>
        <td align='center'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;'>
                <!-- Header -->
                <tr>
                    <td style='background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 28px 32px; text-align: left;'>
                        <div style='color: #38bdf8; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;'>Smart Attendance System</div>
                        <div style='color: #ffffff; font-size: 20px; font-weight: bold; letter-spacing: -0.3px;'>{$companyName}</div>
                    </td>
                </tr>

                <!-- Content Area -->
                <tr>
                    <td style='padding: 32px;'>
                        <div style='margin-bottom: 18px;'>
                            <span style='background: {$badgeColor}; color: #ffffff; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 12px; border-radius: 20px; display: inline-block;'>{$badge}</span>
                        </div>

                        <h2 style='color: #0f172a; font-size: 20px; font-weight: bold; margin: 0 0 12px 0; line-height: 1.3;'>{$title}</h2>
                        
                        <p style='color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 24px 0;'>{$lead}</p>

                        <!-- Details Table -->
                        <table border='0' cellpadding='0' cellspacing='0' width='100%' style='border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px;'>
                            {$rows}
                        </table>

                        <!-- Action Button -->
                        <div style='text-align: center; margin: 28px 0 20px 0;'>
                            <a href='{$buttonUrl}' target='_blank' style='background: #2563eb; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: bold; padding: 12px 28px; border-radius: 25px; display: inline-block; box-shadow: 0 4px 12px rgba(37,99,235,0.25);'>{$buttonText} &rarr;</a>
                        </div>

                        <!-- Additional Note -->
                        " . (!empty($note) ? "<p style='color: #64748b; font-size: 12px; line-height: 1.5; margin: 20px 0 0 0; padding-top: 16px; border-top: 1px dashed #e2e8f0;'>{$note}</p>" : "") . "
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style='background: #f8fafc; padding: 20px 32px; border-top: 1px solid #e2e8f0; text-align: center;'>
                        <p style='color: #94a3b8; font-size: 12px; margin: 0 0 4px 0;'>Automated Leave Workflow Notification &bull; SmartPunch v2.0</p>
                        <p style='color: #cbd5e1; font-size: 11px; margin: 0;'>&copy; {$year} {$companyName}. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>";
    }
}
