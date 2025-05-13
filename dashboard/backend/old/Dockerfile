FROM python:3.10-slim

# Install dependencies
RUN apt-get update && apt-get install -y \
    wget unzip curl gnupg ffmpeg \
    fonts-liberation libappindicator3-1 libasound2 libatk-bridge2.0-0 \
    libnspr4 libnss3 libxss1 libgbm1 libgtk-3-0 libu2f-udev xdg-utils && \
    rm -rf /var/lib/apt/lists/*

# Install Chrome 114
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_114.0.5735.90-1_amd64.deb && \
    apt install -y ./google-chrome-stable_114.0.5735.90-1_amd64.deb && \
    rm google-chrome-stable_114.0.5735.90-1_amd64.deb

# Install ChromeDriver 114
RUN wget -O /tmp/chromedriver.zip https://chromedriver.storage.googleapis.com/114.0.5735.90/chromedriver_linux64.zip && \
    unzip /tmp/chromedriver.zip -d /usr/local/bin/ && \
    chmod +x /usr/local/bin/chromedriver && \
    rm /tmp/chromedriver.zip

# Create app directory
WORKDIR /app
COPY . .

# Install Python dependencies
RUN pip install --no-cache-dir --upgrade pip && \
    pip install fastapi uvicorn selenium requests python-multipart

# Expose port
EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
