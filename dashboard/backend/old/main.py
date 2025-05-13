from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from video_downloader import extract_and_download_video

app = FastAPI()

# Allow frontend calls
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"]
)

class VideoRequest(BaseModel):
    video_id: str
    iframe_url: str

@app.post("/extract-video")
def extract_video(data: VideoRequest):
    try:
        result = extract_and_download_video(data.video_id, data.iframe_url)
        return {"status": "success", **result}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
