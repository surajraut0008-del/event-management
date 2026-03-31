(() => {
  function el(html) {
    const t = document.createElement("template");
    t.innerHTML = html.trim();
    return t.content.firstChild;
  }

  function ensureWidget() {
    if (document.getElementById("chatbotFab")) return;

    document.body.appendChild(el(`
      <button id="chatbotFab" class="btn btn-primary rounded-pill shadow chatbot-fab">
        Chat
      </button>
    `));

    document.body.appendChild(el(`
      <div id="chatbotPanel" class="chatbot-panel">
        <div class="card shadow-lg border-0">
          <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Event Assistant</div>
            <button id="chatbotClose" class="btn btn-sm btn-outline-light">Close</button>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <div class="chat-messages" id="chatMessages"></div>
            <form id="chatForm" class="d-flex gap-2">
              <input id="chatInput" class="form-control" placeholder="Ask: show events / how to book" autocomplete="off" />
              <button class="btn btn-primary">Send</button>
            </form>
            <div class="small muted">
              Chatbot runs on Flask at <code>127.0.0.1:5000</code>.
            </div>
          </div>
        </div>
      </div>
    `));

    const fab = document.getElementById("chatbotFab");
    const panel = document.getElementById("chatbotPanel");
    const closeBtn = document.getElementById("chatbotClose");
    const form = document.getElementById("chatForm");
    const input = document.getElementById("chatInput");
    const messages = document.getElementById("chatMessages");

    function addBubble(text, who) {
      const bubble = document.createElement("div");
      bubble.className = `chat-bubble ${who}`;
      bubble.textContent = text;
      messages.appendChild(bubble);
      messages.scrollTop = messages.scrollHeight;
    }

    function toggle(show) {
      panel.style.display = show ? "block" : "none";
      if (show) input.focus();
    }

    fab.addEventListener("click", () => {
      toggle(panel.style.display !== "block");
      if (messages.childElementCount === 0) {
        addBubble("Hi! Ask me: 'show events' or 'how to book'.", "bot");
      }
    });
    closeBtn.addEventListener("click", () => toggle(false));

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const q = input.value.trim();
      if (!q) return;
      input.value = "";
      addBubble(q, "user");

      try {
        const res = await fetch("http://127.0.0.1:5000/chat", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ message: q })
        });
        const data = await res.json();
        addBubble(data.reply || "Sorry, I couldn't understand that.", "bot");
      } catch (err) {
        addBubble("Chatbot is offline. Start Flask: python chatbot.py", "bot");
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", ensureWidget);
  } else {
    ensureWidget();
  }
})();

