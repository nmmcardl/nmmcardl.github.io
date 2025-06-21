const symbolPlay = '⯈';
const symbolPause = '❚ ❚';
const files = ['Nature-8399','River-655','Waterfall-941','Wave-2737'];

window.addEventListener("DOMContentLoaded", () => {
  const aside = document.querySelector("aside");
  const video = document.getElementById("vidPlayer");
  const playButton = document.getElementById("play");
  const stopButton = document.getElementById("stop");
  const volumeSlider = document.getElementById("volume");
  const progress = document.getElementById("progress");
  const progressFilled = document.getElementById("progressFilled");
  const skipButtons = document.querySelectorAll("[data-skip]");

  files.forEach(file => {
    const img = document.createElement("img");
    img.src = `images/${file}.jpg`;
    img.alt = file;
    img.style.cursor = "pointer";
    img.addEventListener("click", () => {
      video.pause();
      video.src = `videos/${file}.mp4`;
      video.load();
      video.play();
      playButton.textContent = symbolPause;
    });
    aside.appendChild(img);
  });

  playButton.addEventListener("click", () => {
    if (video.paused) {
      video.play();
      playButton.textContent = symbolPause;
    } else {
      video.pause();
      playButton.textContent = symbolPlay;
    }
  });

  stopButton.addEventListener("click", () => {
    video.pause();
    video.currentTime = 0;
    playButton.textContent = symbolPlay;
    updateProgress();
  });

  skipButtons.forEach(button => {
    button.addEventListener("click", () => {
      video.currentTime += parseFloat(button.dataset.skip);
    });
  });

  volumeSlider.addEventListener("input", () => {
    video.volume = volumeSlider.value;
  });

  video.addEventListener("timeupdate", updateProgress);
  
  function updateProgress() {
    const percent = (video.currentTime / video.duration) * 100;
    progressFilled.style.width = `${percent}%`;
  }

  progress.addEventListener("click", (e) => {
    const clickX = e.offsetX;
    const width = progress.offsetWidth;
    const newTime = (clickX / width) * video.duration;
    video.currentTime = newTime;
  });
});