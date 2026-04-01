/* js/faq.js */

document.addEventListener("DOMContentLoaded", () => {
  // FAQ Accordion
  document.querySelectorAll(".faq-question").forEach((question) => {
    question.addEventListener("click", () => {
      const answer = question.nextElementSibling;
      const isActive = question.classList.contains("active");

      // Close all other items
      document.querySelectorAll(".faq-item").forEach((item) => {
        const q = item.querySelector(".faq-question");
        const a = item.querySelector(".faq-answer");
        const svg = q.querySelector("svg");

        if (q !== question) {
          q.classList.remove("active");
          if (svg) svg.classList.remove("rotate-180");
          a.style.maxHeight = "0px";
        }
      });

      // Toggle current item
      const svg = question.querySelector("svg");
      if (!isActive) {
        question.classList.add("active");
        if (svg) svg.classList.add("rotate-180");
        answer.style.maxHeight = answer.scrollHeight + "px";
      } else {
        question.classList.remove("active");
        if (svg) svg.classList.remove("rotate-180");
        answer.style.maxHeight = "0px";
      }
    });
  });

  // Search Filtering
  const faqSearchInput = document.getElementById("faq-search-input");
  if (faqSearchInput) {
    faqSearchInput.addEventListener("input", function () {
      const filter = this.value.toLowerCase();
      document.querySelectorAll(".faq-item").forEach((item) => {
        const question = item
          .querySelector(".faq-question")
          .textContent.toLowerCase();
        const answer = item
          .querySelector(".faq-answer p")
          .textContent.toLowerCase();

        if (question.includes(filter) || answer.includes(filter)) {
          item.style.display = "";
        } else {
          item.style.display = "none";
        }
      });
    });
  }

  // Category navigation (scroll to section)
  document.querySelectorAll(".faq-nav-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-target");
      if (!targetId) return;
      const section = document.getElementById(targetId);
      if (section) {
        section.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });
});
