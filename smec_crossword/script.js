let timer = 600;
let interval;

document.addEventListener("DOMContentLoaded", () => {
    fetchQuestions();
    startTimer();
});

function fetchQuestions() {
    fetch("get_questions.php")
        .then(response => response.json())
        .then(data => {
            displayGrid(data);
        });
}

function startTimer() {
    interval = setInterval(() => {
        if (timer <= 0) {
            submitGame();
        }
        let minutes = Math.floor(timer / 60);
        let seconds = timer % 60;
        document.getElementById("timer").innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        timer--;
    }, 1000);
}

function displayGrid(questions) {
    const grid = document.getElementById("crossword-grid");
    grid.innerHTML = "";

    questions.forEach(q => {
        let input = document.createElement("input");
        input.dataset.answer = q.answer.toUpperCase();
        grid.appendChild(input);
    });
}

document.getElementById("submit-btn").addEventListener("click", submitGame);

function submitGame() {
    clearInterval(interval);
    let score = 0;
    document.querySelectorAll("#crossword-grid input").forEach(input => {
        if (input.value.toUpperCase() === input.dataset.answer) {
            score++;
        }
    });

    let timeTaken = 600 - timer;
    fetch("submit_results.php", {
        method: "POST",
        body: JSON.stringify({ score: score, time_taken: timeTaken }),
        headers: { "Content-Type": "application/json" }
    }).then(response => response.json())
      .then(data => {
          if (data.status === "success") {
              alert("Game submitted!");
              window.location.href = "game.php";
          }
      });
}
