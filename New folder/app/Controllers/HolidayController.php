<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\Holiday;

class HolidayController extends Controller {
    public function index(): void {
        Auth::requireAuth();

        $selectedYear = Request::input('year', date('Y'));
        $holidays = Holiday::all([], 'holiday_date ASC');

        // Group by year and month for display
        $groupedHolidays = [];
        foreach ($holidays as $h) {
            $year = date('Y', strtotime($h['holiday_date']));
            $month = date('F', strtotime($h['holiday_date']));
            $groupedHolidays[$year][$month][] = $h;
        }

        $this->view('holidays.index', [
            'title' => 'Public Holidays Management',
            'holidays' => $holidays,
            'groupedHolidays' => $groupedHolidays,
            'selectedYear' => $selectedYear
        ], 'admin');
    }

    public function store(): void {
        Auth::requireAuth();
        Csrf::verify();

        $title = trim(Request::input('title', ''));
        $holidayDate = trim(Request::input('holiday_date', ''));
        $description = trim(Request::input('description', ''));

        if (empty($title) || empty($holidayDate)) {
            $_SESSION['flash_error'] = 'Holiday title and date are required.';
            redirect('holidays');
        }

        // Check if date already exists
        $existing = Holiday::findByDate($holidayDate);
        if ($existing) {
            $_SESSION['flash_error'] = "A holiday ('{$existing['title']}') already exists on {$holidayDate}.";
            redirect('holidays');
        }

        try {
            Holiday::create([
                'title' => $title,
                'holiday_date' => $holidayDate,
                'description' => $description
            ]);

            Logger::log(Auth::id(), 'HOLIDAY_CREATED', "Added public holiday '{$title}' on {$holidayDate}");
            $_SESSION['flash_success'] = "Public Holiday '{$title}' on {$holidayDate} added successfully!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to add holiday: ' . $e->getMessage();
        }

        redirect('holidays');
    }

    public function update(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $title = trim(Request::input('title', ''));
        $holidayDate = trim(Request::input('holiday_date', ''));
        $description = trim(Request::input('description', ''));

        if ($id <= 0 || empty($title) || empty($holidayDate)) {
            $_SESSION['flash_error'] = 'Valid holiday ID, title, and date are required.';
            redirect('holidays');
        }

        try {
            Holiday::update($id, [
                'title' => $title,
                'holiday_date' => $holidayDate,
                'description' => $description
            ]);

            Logger::log(Auth::id(), 'HOLIDAY_UPDATED', "Updated public holiday #{$id} ('{$title}')");
            $_SESSION['flash_success'] = "Public Holiday '{$title}' updated successfully!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to update holiday: ' . $e->getMessage();
        }

        redirect('holidays');
    }

    public function delete(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        if ($id > 0) {
            $holiday = Holiday::find($id);
            if ($holiday) {
                Holiday::delete($id);
                Logger::log(Auth::id(), 'HOLIDAY_DELETED', "Deleted public holiday '{$holiday['title']}' ({$holiday['holiday_date']})");
                $_SESSION['flash_success'] = "Public Holiday '{$holiday['title']}' deleted successfully.";
            }
        }

        redirect('holidays');
    }
}
