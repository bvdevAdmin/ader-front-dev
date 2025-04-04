
/**
 * @author SIMJAE
 * @param {String} el 필수값(.vplayer) 비디오태그를 감싸고 있는 부모 wrapper
 * @description 비디오 커스텀 컨트롤러
 */
function Vctrbox(el) {
  let videoArr = new Array();
  let elem = document.querySelectorAll(el);
  if (elem === 1) {
    elem = document.querySelector(el);
  } else {
    elem = document.querySelectorAll(el);
  }
  console.log(elem)
  this.el = el;
  this.makeController = (function () {
    elem.forEach((video, idx) => {
      let controllbox = document.createElement("div");
      controllbox.dataset.index = idx;
      controllbox.classList = `vcontroll`;
      controllbox.innerHTML =
        `
            <ul>
              <li class="pause">Pause<span>ll</span></li>
            </ul>
            <ul>
              <li class="progress-bar"><div class="progress-bar-fill"></div></li>
            </ul>
            <ul>
              <li class="time" id="time${idx}" value=""></li>
              <li class="mute">Mute</li>
              <li class="full">Full screen</li>
            </ul>
          `;

      video.appendChild(controllbox);
      video.addEventListener("click", videoEventHandler);

      function setMediaTime(video) {
        var videoTarget = video.querySelector(`video`);
        const duration = Math.floor(videoTarget.duration);
        const current = Math.floor(videoTarget.currentTime);
        let time = duration - current;

        if (current < 1) {
          time = duration;
        }

        const min = parseInt((time % 3600) / 60);
        const sec = time % 60;
        document.getElementById(`time${idx}`).innerHTML = `${min}:${sec < 10 ? "0" + sec : sec}`;
      }

      function updateProgressBar(video) {
        const progressBar = video.querySelector('.progress-bar-fill');
        const videoTarget = video.querySelector('video');
        const progress = (videoTarget.currentTime / videoTarget.duration) * 100;
        progressBar.style.width = `${progress}%`;
      }


      function videoEventHandler(e) {
        const clickTarget = e.target.classList.value;
        const videoTarget = e.currentTarget.querySelector("video");

        if (clickTarget === "pause") {
          togglePlay(videoTarget);
        } else if (clickTarget === "mute") {
          toggleMute(videoTarget);
        } else if (clickTarget === "full") {
          toggleFullScreen(videoTarget);
        }
      }
      function togglePlay(target) {
        const pauseBtn = video.parentNode.parentNode.querySelector(".pause");

        if (target.paused || target.ended) {
          target.play();
          pauseBtn.innerHTML = `Pause<span>ll</span>`;
        } else {
          target.pause();
          pauseBtn.innerHTML = 'Play';
        }
      }

      function toggleMute(target) {
        target.muted = !target.muted;
      }

      function toggleFullScreen(target) {
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else if (document.webkitFullscreenElement) {
          document.webkitExitFullscreen();
        } else if (target.webkitRequestFullscreen) {
          target.webkitRequestFullscreen();
        } else {
          target.requestFullscreen();
        }
      }
      videoArr.push(video);
      setInterval(() => setMediaTime(video), 1000);
      setInterval(() => updateProgressBar(video), 100);
      return videoArr;
    });
  })();
}

function getLanguage() {
  let local_lng = localStorage.getItem('lang');
  if (!local_lng) {
    let country = navigator.language || navigator.userLanguage;
    switch (country) {
      case "ko-KR":
        local_lng = "KR";
        break;

      case "zh-CN":
        local_lng = "CN";
        break;

      default:
        local_lng = "EU";
        break
    }
  }

  return local_lng;
}