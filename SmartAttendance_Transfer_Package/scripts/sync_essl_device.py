"""
========================================================================================
 GANNON DUNKERLEY & CO. LTD. - SMART ATTENDANCE BIOMETRIC SYNC AGENT
 Connects to local eSSL / ZKTeco Face & Biometric Machines on LAN / Wi-Fi
 and synchronizes all enrolled employees & live attendance punches to Cloud Portal.
========================================================================================
Requirements:
    pip install zk pyzk requests urllib3

Usage:
    python sync_essl_device.py
========================================================================================
"""

import sys
import time
import requests
import json

try:
    from zk import ZK, const
except ImportError:
    print("Installing required 'pyzk' and 'requests' library...")
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "pyzk", "requests"])
    from zk import ZK, const

# --- CONFIGURATION ---
PORTAL_URL = "https://smart-attendance-hw9c.onrender.com/api/device/sync-all"
DEFAULT_DEVICE_IP = "192.168.1.201"   # Change to your eSSL machine's local IP
DEFAULT_DEVICE_PORT = 4370            # Default eSSL/ZKTeco port is 4370
SYNC_INTERVAL_SECONDS = 10            # Checks for new punches every 10 seconds

def run_sync():
    print("=" * 70)
    print(" GANNON DUNKERLEY & CO. LTD. — BIOMETRIC SYNC AGENT")
    print(" Cloud Portal Target:", PORTAL_URL)
    print("=" * 70)

    device_ip = input(f"Enter eSSL Machine IP Address [{DEFAULT_DEVICE_IP}]: ").strip() or DEFAULT_DEVICE_IP
    site_name = input("Enter Assigned Work Site [Head Office]: ").strip() or "Head Office"
    project_name = input("Enter Project Name [General]: ").strip() or "General"

    print(f"\n[INFO] Connecting to eSSL Device at {device_ip}:{DEFAULT_DEVICE_PORT}...")
    zk = ZK(device_ip, port=DEFAULT_DEVICE_PORT, timeout=10, password=0, force_udp=False, ommit_ping=False)

    conn = None
    try:
        conn = zk.connect()
        print("[SUCCESS] Connected to eSSL Biometric Terminal!")
        
        # Get Device Info
        sn = conn.get_serialnumber() or f"ESSL-{device_ip.replace('.', '')}"
        device_name = conn.get_device_name() or "eSSL Face Terminal"
        fw_ver = conn.get_firmware_version() or "Ver 2.4"
        print(f"[INFO] Device SN: {sn} | Model: {device_name} | Firmware: {fw_ver}")

        # 1. First Sync: Pull all Enrolled Employees from Device
        print("\n[INFO] Reading enrolled employees from machine memory...")
        users = conn.get_users()
        print(f"[INFO] Found {len(users)} enrolled employee(s) in device.")

        user_payload = []
        for u in users:
            user_payload.append({
                "user_id": str(u.user_id),
                "name": str(u.name or f"Employee {u.user_id}"),
                "card": str(u.card or ""),
                "privilege": u.privilege
            })

        # 2. Read Attendance Logs
        print("[INFO] Reading attendance log history from device...")
        logs = conn.get_attendance()
        print(f"[INFO] Found {len(logs)} attendance punch record(s).")

        punch_payload = []
        for l in logs:
            punch_payload.append({
                "user_id": str(l.user_id),
                "timestamp": l.timestamp.strftime("%Y-%m-%d %H:%M:%S"),
                "status": str(l.status),
                "punch_type": "IN" if l.status in (0, 3, 4) else "OUT"
            })

        # Post initial sync payload to Cloud Portal
        print(f"\n[INFO] Uploading data to Cloud Portal ({PORTAL_URL})...")
        res = requests.post(PORTAL_URL, json={
            "sn": sn,
            "device_ip": device_ip,
            "device_name": device_name,
            "device_model": "eSSL Face / Fingerprint Terminal",
            "site": site_name,
            "project": project_name,
            "users": user_payload,
            "punches": punch_payload
        }, timeout=20)

        if res.status_code == 200:
            res_data = res.json()
            print(f"[SUCCESS] {res_data.get('message', 'Sync Complete!')}")
        else:
            print(f"[ERROR] Server responded with HTTP {res.status_code}: {res.text}")

        # 3. Continuous Live Monitoring Loop
        print("\n" + "-" * 70)
        print("[LIVE AUTO-SYNC] Now monitoring real-time punches every 10 seconds.")
        print("Keep this window open on your office computer.")
        print("Press Ctrl+C to stop.")
        print("-" * 70)

        last_sync_time = time.time()
        while True:
            time.sleep(SYNC_INTERVAL_SECONDS)
            try:
                # Fetch recent attendance records
                live_logs = conn.get_attendance()
                if live_logs:
                    # Filter punches from last 10 minutes
                    recent = [
                        {
                            "user_id": str(l.user_id),
                            "timestamp": l.timestamp.strftime("%Y-%m-%d %H:%M:%S"),
                            "status": str(l.status),
                            "punch_type": "IN" if l.status in (0, 3, 4) else "OUT"
                        }
                        for l in live_logs
                    ]
                    
                    r = requests.post(PORTAL_URL, json={
                        "sn": sn,
                        "device_ip": device_ip,
                        "device_name": device_name,
                        "site": site_name,
                        "project": project_name,
                        "users": [],
                        "punches": recent[-20:] # Send latest 20 punches
                    }, timeout=15)
                    
                    if r.status_code == 200:
                        data = r.json()
                        if data.get('synced_punches', 0) > 0:
                            print(f"[{time.strftime('%H:%M:%S')}] ⚡ Synced {data['synced_punches']} new punch(es) to web portal!")
            except Exception as e:
                print(f"[{time.strftime('%H:%M:%S')}] Sync heartbeat check...")

    except Exception as e:
        print(f"\n[ERROR] Connection failed: {e}")
        print("\nTroubleshooting:")
        print("1. Check if the eSSL machine IP is correct (Menu > Comm. > Ethernet).")
        print("2. Make sure this PC is connected to the same Wi-Fi / Router as the eSSL machine.")
        print("3. Try pinging the machine IP from Command Prompt: ping", device_ip)
    finally:
        if conn:
            try:
                conn.disconnect()
            except:
                pass

if __name__ == "__main__":
    run_sync()
