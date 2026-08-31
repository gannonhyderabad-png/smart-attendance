import time
import requests
import json
from datetime import datetime

DEVICE_IP = "192.168.1.201"
DEVICE_PORT = 4370
SERVER_URL = "https://smart-attendance-hw9c.onrender.com/api/biometric/push"
SYNC_INTERVAL = 5

def main():
    print("=" * 65)
    print(" SmartPunch eSSL / ZKTeco Biometric Live Sync Bridge")
    print(f" Target Device IP: {DEVICE_IP}:{DEVICE_PORT}")
    print(f" Cloud Server URL: {SERVER_URL}")
    print("=" * 65)
    try:
        from zk import ZK
    except ImportError:
        print("\n[!] 'pyzk' library is not installed.")
        print("[!] Please run: pip install pyzk requests\n")
        return

    zk = ZK(DEVICE_IP, port=DEVICE_PORT, timeout=5, password=0, force_udp=False, ommit_ping=False)
    while True:
        try:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] Checking eSSL machine ({DEVICE_IP})...", end="\r")
            conn = zk.connect()
            conn.disable_device()
            sn = conn.get_serialnumber() or f"DEV-{DEVICE_IP}"
            logs = conn.get_attendance()
            if logs:
                new_punches = []
                for att in logs:
                    punch_time_str = att.timestamp.strftime("%Y-%m-%d %H:%M:%S") if hasattr(att.timestamp, "strftime") else str(att.timestamp)
                    new_punches.append({
                        "employee_code": str(att.user_id),
                        "punch_time": punch_time_str,
                        "punch_type": "IN" if att.punch == 0 else "OUT"
                    })
                if new_punches:
                    payload = {"device_ip": DEVICE_IP, "serial_number": sn, "punches": new_punches}
                    resp = requests.post(SERVER_URL, json=payload, headers={"Content-Type": "application/json"}, timeout=10)
                    if resp.status_code == 200:
                        print(f"\n[{datetime.now().strftime('%H:%M:%S')}] Synced {len(new_punches)} punches to cloud successfully!")
            conn.enable_device()
            conn.disconnect()
        except Exception as e:
            print(f"\n[!] Connection error with {DEVICE_IP}: {e}")
        time.sleep(SYNC_INTERVAL)

if __name__ == "__main__":
    main()
