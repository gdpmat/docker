import os
import time
import subprocess
from selenium import webdriver
from selenium.webdriver.chrome.options import Options

def extract_and_download_video(video_id, iframe_url):
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--autoplay-policy=no-user-gesture-required")
    chrome_options.binary_location = "/usr/bin/google-chrome"
    chrome_options.set_capability("goog:loggingPrefs", {"performance": "ALL"})

    driver = webdriver.Chrome(options=chrome_options)
    driver.get(iframe_url)
    time.sleep(10)

    logs = driver.get_log("performance")
    driver.quit()

    m3u8_url = None
    for entry in logs:
        msg = entry["message"]
        if ".m3u8" in msg:
            start = msg.find("https")
            end = msg.find(".m3u8") + 5
            m3u8_url = msg[start:end]
            break

    if not m3u8_url:
        raise Exception("No m3u8 link found")

    output_path = f"video/{video_id}.mp4"
    os.makedirs("video", exist_ok=True)
    subprocess.run(["ffmpeg", "-y", "-i", m3u8_url, "-c", "copy", output_path], check=True)

    cmd_duration = [
        "ffprobe", "-v", "error", "-show_entries",
        "format=duration", "-of", "default=noprint_wrappers=1:nokey=1", output_path
    ]
    duration_output = subprocess.check_output(cmd_duration).decode().strip()
    return {
        "filename": f"{video_id}.mp4",
        "duration": duration_output
    }
