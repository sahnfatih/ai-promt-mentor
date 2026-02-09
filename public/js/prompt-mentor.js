// Basit state
const state = {
  currentPrompt: "",
  previousPrompt: "",
  negativePrompt: "",
  realismScore: 0,
  stage: 1,
  changes: [], // Her turda eklenen değişiklikler
  sessionSummary: {
    style: null,
    lighting: null,
    lens: null,
    environment: null,
  },
};

// DOM referansları
const chatContainer = document.getElementById("chat-container");
const inputEl = document.getElementById("user-input");
const sendBtn = document.getElementById("send-btn");
const loadingIndicator = document.getElementById("loading-indicator");
const realismBar = document.getElementById("realism-bar");
const realismLabel = document.getElementById("realism-label");
const realismHelperText = document.getElementById("realism-helper-text");
const promptPreview = document.getElementById("prompt-preview");
const copyFinalBtn = document.getElementById("copy-final-btn");
const finalHelperText = document.getElementById("final-helper-text");
const negativePromptPreview = document.getElementById(
  "negative-prompt-preview"
);
const copyNegativeBtn = document.getElementById("copy-negative-btn");
const stepperStages = document.querySelectorAll("[data-stepper-stage]");
const presetsContainer = document.getElementById("preset-container");

const chatSection = document.querySelector("section[data-chat-id]");
const chatId = chatSection
  ? parseInt(chatSection.getAttribute("data-chat-id") || "0", 10)
  : 0;

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
}

function scrollChatToBottom() {
  if (!chatContainer) return;
  chatContainer.scrollTo({
    top: chatContainer.scrollHeight,
    behavior: "smooth",
  });
}

function appendUserMessage(text) {
  if (!chatContainer) return;
  const wrapper = document.createElement("div");
  wrapper.className = "flex justify-end";
  const bubble = document.createElement("div");
  bubble.className =
    "max-w-[80%] rounded-2xl rounded-br-sm bg-emerald-500 text-slate-950 px-3.5 py-2.5 text-xs lg:text-sm whitespace-pre-wrap shadow-md shadow-emerald-500/30";
  bubble.textContent = text;
  wrapper.appendChild(bubble);
  chatContainer.appendChild(wrapper);
  scrollChatToBottom();
}

function appendAssistantMessage(text) {
  if (!chatContainer) return;
  const wrapper = document.createElement("div");
  wrapper.className = "flex justify-start";
  const bubble = document.createElement("div");
  bubble.className =
    "max-w-[85%] rounded-2xl rounded-bl-sm bg-slate-900/80 border border-slate-700/70 px-3.5 py-2.5 text-xs lg:text-sm text-slate-100 whitespace-pre-wrap shadow-md shadow-black/40";
  bubble.textContent = text;
  wrapper.appendChild(bubble);
  chatContainer.appendChild(wrapper);
  scrollChatToBottom();
}

function setLoading(isLoading) {
  if (!loadingIndicator || !sendBtn) return;
  if (isLoading) {
    loadingIndicator.classList.remove("hidden");
    loadingIndicator.classList.add("flex");
    sendBtn.disabled = true;
  } else {
    loadingIndicator.classList.add("hidden");
    loadingIndicator.classList.remove("flex");
    sendBtn.disabled = false;
  }
}

function clearInput() {
  if (inputEl) inputEl.value = "";
}

function updateRealismMeter(score) {
  const clamped = Math.min(100, Math.max(0, score || 0));
  if (realismBar) realismBar.style.width = `${clamped}%`;
  if (realismLabel) realismLabel.textContent = `${clamped}%`;

  let helper;
  if (clamped < 30) {
    helper =
      "Şu an fikir çok kaba. Sahne, ışık ve ortamı netleştirerek başlayalım.";
  } else if (clamped < 60) {
    helper =
      "Güzel gidiyoruz, hala biraz yapay. Kamera, lens ve kompozisyonu keskinleştirelim.";
  } else if (clamped < 85) {
    helper =
      "Artık sinematik bir seviyeye yaklaşıyoruz. Küçük teknik dokunuşlar gerçekçiliği artıracak.";
  } else if (clamped < 95) {
    helper =
      "Neredeyse hazır! Son birkaç ayrıntıyla profesyonel prodüksiyon seviyesine çıkacağız.";
  } else {
    helper =
      "Bu prompt artık profesyonel kullanım için hazır. Dilersen kopyalayıp doğrudan kullanabilirsin.";
  }
  if (realismHelperText) realismHelperText.textContent = helper;
}

function updateNegativePrompt(text) {
  if (!negativePromptPreview) return;
  if (!text) {
    negativePromptPreview.textContent =
      "Bozuk anatomi, plastik cilt, watermark, logo, aşırı gürültü gibi istenmeyen detaylar burada derlenir.";
  } else {
    negativePromptPreview.textContent = text;
  }
}

function updateStepper(stageIndex) {
  const active = Number(stageIndex) || 1;
  stepperStages.forEach((el) => {
    const s = Number(el.getAttribute("data-stepper-stage") || "1");
    if (s <= active) {
      el.classList.remove("opacity-70");
      el.classList.add(
        "border-emerald-400",
        "bg-emerald-500/5",
        "text-emerald-200"
      );
      const dot = el.querySelector("span");
      if (dot) {
        dot.classList.remove("bg-slate-700", "bg-slate-500");
        dot.classList.add("bg-emerald-400");
      }
    } else {
      el.classList.add("opacity-70");
      el.classList.remove(
        "border-emerald-400",
        "bg-emerald-500/5",
        "text-emerald-200"
      );
      const dot = el.querySelector("span");
      if (dot) {
        dot.classList.remove("bg-emerald-400");
        dot.classList.add("bg-slate-700");
      }
    }
  });
}

function escapeHtml(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function extractChanges(oldText, newText) {
  if (!oldText) return [];

  const added = newText.slice(oldText.length);
  if (!added.trim()) return [];

  // Teknik terimleri ve önemli kelimeleri çıkar
  const technicalTerms = [
    /\b\d{2,3}mm\b/gi,
    /\bf\/\d+(\.\d+)?\b/gi,
    /\bISO\s*\d+\b/gi,
    /\bgolden hour\b/gi,
    /\bcinematic\b/gi,
    /\bstudio lighting\b/gi,
    /\bdepth of field\b/gi,
    /\bbokeh\b/gi,
    /\bHDR\b/gi,
  ];

  const changes = [];
  technicalTerms.forEach((pattern) => {
    const matches = added.match(pattern);
    if (matches) {
      changes.push(...matches);
    }
  });

  // Eğer teknik terim yoksa, cümleleri ayır
  if (changes.length === 0) {
    const sentences = added.split(/[.,;]/).filter(s => s.trim().length > 10);
    changes.push(...sentences.slice(0, 3).map(s => s.trim()));
  }

  return [...new Set(changes)].slice(0, 5); // Maksimum 5 değişiklik
}

function updatePromptPreview(previousPrompt, newPrompt) {
  if (!promptPreview) return;
  if (!newPrompt) {
    promptPreview.textContent =
      "Burada profesyonel promptun canlı olarak oluşacak. Önce fikrini yaz, sonra eksik detayları birlikte dolduralım.";
    return;
  }

  const oldText = previousPrompt || "";
  const text = newPrompt || "";

  // Değişiklikleri track et
  const changes = extractChanges(oldText, text);
  if (changes.length > 0) {
    state.changes.push({
      timestamp: new Date(),
      items: changes,
    });
    updateChangesTab();
  }

  if (!oldText) {
    promptPreview.innerHTML = `<span class="text-emerald-300" data-new-part="1">${escapeHtml(
      text
    )}</span>`;
  } else {
    let idx = 0;
    const minLen = Math.min(oldText.length, text.length);
    while (idx < minLen && oldText[idx] === text[idx]) {
      idx++;
    }

    const unchanged = escapeHtml(text.slice(0, idx));
    const added = escapeHtml(text.slice(idx));

    promptPreview.innerHTML = `${unchanged}<span class="text-emerald-300" data-new-part="1">${added}</span>`;
  }

  const highlightEl = promptPreview.querySelector("[data-new-part]");
  if (highlightEl) {
    highlightEl.classList.add("animate-pulse");
    setTimeout(() => {
      highlightEl.classList.remove("animate-pulse");
      highlightEl.removeAttribute("data-new-part");
    }, 2000);
  }
}

function updateChangesTab() {
  const changesList = document.getElementById("changes-list");
  if (!changesList) return;

  if (state.changes.length === 0) {
    changesList.innerHTML = '<div class="text-slate-500 text-[10px]">Henüz değişiklik yok.</div>';
    return;
  }

  // Son 5 değişikliği göster
  const recentChanges = state.changes.slice(-5).reverse();
  changesList.innerHTML = recentChanges.map((change, idx) => {
    const time = new Date(change.timestamp).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
    return `
      <div class="text-[10px] text-slate-400 mb-2">
        <div class="text-emerald-300 mb-1">${time}</div>
        <ul class="list-disc list-inside space-y-0.5 text-slate-300">
          ${change.items.map(item => `<li>${escapeHtml(item)}</li>`).join('')}
        </ul>
      </div>
    `;
  }).join('');
}


function bindCopyButtons() {
  if (copyFinalBtn) {
    copyFinalBtn.addEventListener("click", async () => {
      if (!state.currentPrompt) return;
      try {
        await navigator.clipboard.writeText(state.currentPrompt);
        const original = copyFinalBtn.textContent;
        copyFinalBtn.textContent = "Kopyalandı!";
        copyFinalBtn.classList.add("bg-emerald-500/10");
        setTimeout(() => {
          copyFinalBtn.textContent = original;
          copyFinalBtn.classList.remove("bg-emerald-500/10");
        }, 1400);
      } catch {
        alert("Kopyalama başarısız oldu, lütfen manuel olarak kopyala.");
      }
    });
  }

  if (copyNegativeBtn) {
    copyNegativeBtn.addEventListener("click", async () => {
      if (!state.negativePrompt) return;
      try {
        await navigator.clipboard.writeText(state.negativePrompt);
        const original = copyNegativeBtn.textContent;
        copyNegativeBtn.textContent = "Kopyalandı!";
        copyNegativeBtn.classList.add("bg-rose-500/10");
        setTimeout(() => {
          copyNegativeBtn.textContent = original;
          copyNegativeBtn.classList.remove("bg-rose-500/10");
        }, 1400);
      } catch {
        alert("Kopyalama başarısız oldu, lütfen manuel olarak kopyala.");
      }
    });
  }
}

function computeRealismScore(promptText, baseScore) {
  const base = Number.isFinite(baseScore) ? baseScore : 0;
  const text = (promptText || "").toLowerCase();

  const patterns = [
    /f\/\d+(\.\d+)?/g,
    /\biso\s*\d{2,5}\b/g,
    /\b\d{2,3}mm\b/g,
    /\bshutter\s*speed\b/g,
    /\b1\/\d{2,5}\b/g,
    /\b(depth of field|dof|bokeh)\b/g,
    /\b(hdr|high dynamic range)\b/g,
    /\b(cinema(tic)? lighting|studio lighting|softbox|three point lighting)\b/g,
    /\bgrain(y)? texture\b/g,
  ];

  let technicalCount = 0;
  patterns.forEach((re) => {
    const matches = text.match(re);
    if (matches) technicalCount += matches.length;
  });

  const bonus = Math.min(40, technicalCount * 5);
  const score = Math.min(100, Math.max(0, base + bonus));
  return score;
}

function stageFromScore(score) {
  if (score < 30) return 1;
  if (score < 60) return 2;
  if (score < 90) return 3;
  return 4;
}

function detectQuestionCategory(question) {
  const q = (question || "").toLowerCase();
  if (!q) return null;

  if (q.includes("ışık") || q.includes("aydınlatma") || q.includes("light")) {
    return "lighting";
  }
  if (q.includes("kamera") || q.includes("lens") || q.includes("açı")) {
    return "camera";
  }
  if (q.includes("renk") || q.includes("palet") || q.includes("mood")) {
    return "mood";
  }
  if (q.includes("arka plan") || q.includes("background") || q.includes("ortam")) {
    return "environment";
  }
  return null;
}

function getPresetsForCategory(category) {
  switch (category) {
    case "lighting":
      return [
        {
          label: "Altın Saat",
          value: "golden hour soft sunlight with long shadows",
        },
        {
          label: "Stüdyo Işığı",
          value: "studio lighting with softboxes and clean background",
        },
        {
          label: "Sisli Sabah",
          value: "foggy early morning with diffused, low contrast light",
        },
      ];
    case "camera":
      return [
        {
          label: "Portre 85mm f/1.8",
          value: "shot on 85mm lens at f/1.8, shallow depth of field",
        },
        {
          label: "Geniş Açı 24mm",
          value: "shot on 24mm wide angle lens, emphasizing perspective",
        },
        {
          label: "Tele 135mm",
          value: "shot on 135mm telephoto lens, compressed background",
        },
      ];
    case "mood":
      return [
        {
          label: "Sinematik Soğuk",
          value: "cinematic cool color grading with teal and orange contrast",
        },
        {
          label: "Sıcak Nostaljik",
          value: "warm nostalgic tones with subtle film grain",
        },
        {
          label: "Neon Cyberpunk",
          value: "neon cyberpunk palette with magenta and cyan highlights",
        },
      ];
    case "environment":
      return [
        {
          label: "Orman Sisi",
          value: "dense forest with volumetric fog and light rays",
        },
        {
          label: "Şehir Gece Yağmurlu",
          value:
            "rainy city street at night with reflections on wet asphalt and neon signs",
        },
        {
          label: "Minimal Stüdyo",
          value: "minimal studio backdrop with seamless paper",
        },
      ];
    default:
      return [];
  }
}

function renderPresets(presets, onSelect) {
  if (!presetsContainer) return;
  if (!presets || presets.length === 0) {
    presetsContainer.classList.add("hidden");
    presetsContainer.innerHTML = "";
    return;
  }

  presetsContainer.classList.remove("hidden");
  presetsContainer.innerHTML = "";

  presets.forEach((preset) => {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className =
      "inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full border border-slate-700/80 bg-slate-900/70 hover:border-emerald-400 hover:text-emerald-300 text-[11px] lg:text-[11px] transition";
    btn.textContent = preset.label;
    btn.addEventListener("click", () => {
      if (inputEl) {
        inputEl.value = preset.value;
      }
      onSelect?.(preset.value);
    });
    presetsContainer.appendChild(btn);
  });
}

function clearPresets() {
  if (!presetsContainer) return;
  presetsContainer.classList.add("hidden");
  presetsContainer.innerHTML = "";
}

async function sendToBackend(message, template = null) {
  const csrf = getCsrfToken();
  const body = {
    chat_id: chatId,
    message,
  };

  if (template) {
    body.template = template;
  }

  const res = await fetch("/prompt-mentor", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrf,
      Accept: "application/json",
    },
    body: JSON.stringify(body),
  });

  const data = await res.json();

  // 429 hatası için özel işleme
  if (!res.ok && res.status === 429) {
    data.errorCode = 429;
  }

  return data;
}

let lastPrompt = "";

async function handleUserMessage(message, template = null, isRetry = false) {
  if (!message.trim()) return;

  // Retry değilse kullanıcı mesajını göster ve input'u temizle
  if (!isRetry) {
    appendUserMessage(message);
    clearInput();
    cancelRetry(); // Önceki retry'ı iptal et
  }

  setLoading(true);
  clearPresets();

  try {
    const response = await sendToBackend(message, template);

    // Rate limit hatası kontrolü
    if (response.errorCode === 429) {
      setLoading(false);
      handleRateLimitError(response, message);
      return;
    }

    const {
      assistantMessage,
      nextQuestion,
      currentPrompt,
      realismScore,
      isFinal,
      negativePrompt,
    } = response;

    // Başarılı ise retry sayacını sıfırla
    retryCount = 0;

    if (assistantMessage) {
      appendAssistantMessage(assistantMessage);
    }

    const newPrompt = currentPrompt || state.currentPrompt;
    const newNegative = negativePrompt || state.negativePrompt;

    updatePromptPreview(state.currentPrompt, newPrompt);
    updateNegativePrompt(newNegative);

    const effectiveRealism = computeRealismScore(newPrompt, realismScore);
    state.previousPrompt = state.currentPrompt;
    state.currentPrompt = newPrompt;
    state.negativePrompt = newNegative;
    state.realismScore = effectiveRealism;
    state.stage = stageFromScore(effectiveRealism);

    updateRealismMeter(effectiveRealism);
    updateStepper(state.stage);
    setFinalState(isFinal || effectiveRealism >= 90, newPrompt, newNegative);

    // Session summary güncelle
    if (isFinal || effectiveRealism >= 90) {
      updateSessionSummary(newPrompt);
    }

    lastPrompt = newPrompt || lastPrompt;

    if (!isFinal && effectiveRealism < 95 && nextQuestion) {
      appendAssistantMessage(nextQuestion);
      const category = detectQuestionCategory(nextQuestion);
      const presets = getPresetsForCategory(category);
      if (presets.length) {
        renderPresets(presets, (value) => {
          handleUserMessage(value);
        });
      }
    } else if (isFinal && currentPrompt) {
      appendAssistantMessage(
        "Bu prompt artık profesyonel kullanım için hazır. Dilersen doğrudan kopyalayıp kullanabilirsin."
      );
    }
  } catch (err) {
    console.error("Prompt mentor isteği hatası:", err);
    appendAssistantMessage(
      "Gemini ile konuşurken bir sorun oluştu. Lütfen birkaç saniye sonra tekrar dene."
    );
  } finally {
    setLoading(false);
  }
}

// Rate limit hatası için otomatik retry mekanizması
let retryTimeout = null;
let retryCount = 0;
const MAX_RETRIES = 3;

function handleRateLimitError(response, originalMessage) {
  const retryAfter = response.retryAfter || 10; // Varsayılan 10 saniye
  retryCount++;

  if (retryCount > MAX_RETRIES) {
    appendAssistantMessage(
      "❌ Çok fazla deneme yapıldı. Lütfen birkaç dakika bekleyip tekrar deneyin."
    );
    retryCount = 0;
    setLoading(false);
    return;
  }

  // Exponential backoff: 10s, 20s, 40s
  const waitSeconds = retryAfter * Math.pow(2, retryCount - 1);

  // Kullanıcıya bilgi ver
  appendAssistantMessage(
    `⏳ Rate limit nedeniyle ${waitSeconds} saniye bekleniyor... (Otomatik deneme ${retryCount}/${MAX_RETRIES})`
  );

  // Otomatik retry
  retryTimeout = setTimeout(() => {
    handleUserMessage(originalMessage, null, true);
  }, waitSeconds * 1000);
}

function cancelRetry() {
  if (retryTimeout) {
    clearTimeout(retryTimeout);
    retryTimeout = null;
  }
  retryCount = 0;
}

function bindSend() {
  if (!sendBtn || !inputEl) return;

  sendBtn.addEventListener("click", () => {
    const value = inputEl.value.trim();
    if (!value) return;
    handleUserMessage(value);
  });

  inputEl.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      const value = inputEl.value.trim();
      if (!value) return;
      handleUserMessage(value);
    }
  });
}

function initIntro() {
  if (!chatContainer) return;
  if (chatContainer.children.length === 0) {
    appendAssistantMessage(
      "Ben senin sanat yönetmeninim. Basit fikrini birlikte sinematik, profesyonel ve Midjourney/DALL·E seviyesinde bir prompta dönüştüreceğiz.\n\nÖnce aklındaki fikri sade bir cümleyle yaz: sahne, ana özne ve ortam."
    );
  }
}

function initChatManagement() {
  // Close modals on outside click
  const editModal = document.getElementById("edit-chat-modal");
  const deleteModal = document.getElementById("delete-chat-modal");

  if (editModal) {
    editModal.addEventListener("click", (e) => {
      if (e.target === editModal) {
        editModal.classList.add("hidden");
      }
    });
  }

  if (deleteModal) {
    deleteModal.addEventListener("click", (e) => {
      if (e.target === deleteModal) {
        deleteModal.classList.add("hidden");
      }
    });
  }

  // Edit chat buttons
  document.querySelectorAll(".chat-edit-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const chatId = btn.getAttribute("data-chat-id");
      const chatTitle = btn.getAttribute("data-chat-title");
      const chatToken = document
        .querySelector(`[data-chat-id="${chatId}"]`)
        ?.closest("[data-chat-token]")
        ?.getAttribute("data-chat-token");

      if (!chatToken) return;

      const modal = document.getElementById("edit-chat-modal");
      const form = document.getElementById("edit-chat-form");
      const input = document.getElementById("edit-chat-title-input");
      const cancelBtn = document.getElementById("cancel-edit-btn");

      input.value = chatTitle;
      form.action = `/chats/${chatToken}`;
      modal.classList.remove("hidden");
      input.focus();
      input.select();

      const closeModal = () => {
        modal.classList.add("hidden");
        form.removeEventListener("submit", handleSubmit);
        cancelBtn.removeEventListener("click", closeModal);
      };

      cancelBtn.addEventListener("click", closeModal, { once: true });

      const handleSubmit = async (e) => {
        e.preventDefault();
        const csrf = getCsrfToken();
        const newTitle = input.value.trim();

        if (!newTitle) return;

        try {
          const res = await fetch(form.action, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": csrf,
              Accept: "application/json",
            },
            body: JSON.stringify({ title: newTitle }),
          });

          if (res.ok) {
            const data = await res.json();
            const titleEl = document.querySelector(
              `.chat-title[data-chat-id="${chatId}"]`
            );
            if (titleEl) {
              titleEl.textContent = data.chat.title;
            }
            // Update button attribute
            btn.setAttribute("data-chat-title", data.chat.title);
            closeModal();
            // Reload page to update sidebar
            window.location.reload();
          } else {
            alert("Sohbet başlığı güncellenirken bir hata oluştu.");
          }
        } catch (err) {
          console.error("Edit chat error:", err);
          alert("Bir hata oluştu, lütfen tekrar deneyin.");
        }
      };

      form.addEventListener("submit", handleSubmit);

    });
  });

  // Delete chat buttons
  document.querySelectorAll(".chat-delete-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const chatId = btn.getAttribute("data-chat-id");
      const chatToken = btn.getAttribute("data-chat-token");
      const chatTitle = btn.getAttribute("data-chat-title");

      const modal = document.getElementById("delete-chat-modal");
      const form = document.getElementById("delete-chat-form");
      const titleSpan = document.getElementById("delete-chat-title");
      const cancelBtn = document.getElementById("cancel-delete-btn");

      titleSpan.textContent = chatTitle;
      form.action = `/chats/${chatToken}`;
      modal.classList.remove("hidden");

      const closeModal = () => {
        modal.classList.add("hidden");
        form.removeEventListener("submit", handleSubmit);
        cancelBtn.removeEventListener("click", closeModal);
      };

      cancelBtn.addEventListener("click", closeModal, { once: true });

      const handleSubmit = async (e) => {
        e.preventDefault();
        const csrf = getCsrfToken();

        try {
          const res = await fetch(form.action, {
            method: "DELETE",
            headers: {
              "X-CSRF-TOKEN": csrf,
              Accept: "application/json",
            },
          });

          if (res.ok || res.redirected) {
            // Redirect to dashboard
            window.location.href = "/dashboard";
          } else {
            alert("Sohbet silinirken bir hata oluştu.");
          }
        } catch (err) {
          console.error("Delete chat error:", err);
          alert("Bir hata oluştu, lütfen tekrar deneyin.");
        }
      };

      form.addEventListener("submit", handleSubmit);
    });
  });
}

function initPresetTemplates() {
  const container = document.getElementById("preset-templates-container");
  if (!container) return;

  // Sadece ilk mesajda göster
  if (chatContainer && chatContainer.children.length === 0) {
    container.classList.remove("hidden");
  }

  const templates = {
    portrait: "Profesyonel bir portre fotoğrafı oluştur: yumuşak ışık, sıcak tonlar, sığ alan derinliği",
    cityscape: "Şehir manzarası fotoğrafı: gece veya gündüz, sinematik kompozisyon, detaylı mimari",
    product: "Ürün fotoğrafçılığı: temiz stüdyo ışığı, minimal arka plan, profesyonel kompozisyon",
    cinematic: "Film sahnesi görseli: sinematik ışıklandırma, dramatik kompozisyon, sinema kalitesi",
  };

  document.querySelectorAll(".preset-template-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const template = btn.getAttribute("data-template");
      const message = templates[template];
      if (message && inputEl) {
        inputEl.value = message;
        container.classList.add("hidden");
        // Template bilgisini state'e kaydet
        state.currentTemplate = template;
        handleUserMessage(message, template);
      }
    });
  });
}

function initStepperNavigation() {
  document.querySelectorAll(".stepper-stage-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const stage = parseInt(btn.getAttribute("data-stepper-stage") || "1");
      // Bu özellik için backend'de stage bazlı mesaj filtreleme gerekir
      // Şimdilik sadece UI feedback veriyoruz
      alert(`Aşama ${stage} seçildi. Bu özellik yakında aktif olacak.`);
    });
  });
}

function initTabSwitching() {
  const tabButtons = document.querySelectorAll(".prompt-tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetTab = btn.getAttribute("data-tab");

      // Tüm tabları deaktif et
      tabButtons.forEach((b) => {
        b.classList.remove("active", "text-emerald-300", "border-emerald-400");
        b.classList.add("text-slate-400", "border-transparent");
      });

      // Aktif tabı işaretle
      btn.classList.add("active", "text-emerald-300", "border-emerald-400");
      btn.classList.remove("text-slate-400", "border-transparent");

      // Tüm içerikleri gizle
      tabContents.forEach((content) => {
        content.classList.add("hidden");
      });

      // Hedef içeriği göster
      const targetContent = document.getElementById(`${targetTab}-tab-content`);
      if (targetContent) {
        targetContent.classList.remove("hidden");
      }
    });
  });
}

function updateSessionSummary(promptText) {
  const summaryCard = document.getElementById("session-summary-card");
  const summaryContent = document.getElementById("session-summary-content");
  if (!summaryCard || !summaryContent) return;

  // Prompt'tan bilgileri çıkar
  const text = promptText.toLowerCase();

  // Stil
  if (text.includes("cinematic") || text.includes("sinematik")) {
    state.sessionSummary.style = "Sinematik";
  } else if (text.includes("warm") || text.includes("sıcak")) {
    state.sessionSummary.style = "Sıcak";
  } else if (text.includes("cool") || text.includes("soğuk")) {
    state.sessionSummary.style = "Soğuk";
  }

  // Işık
  if (text.includes("golden hour")) {
    state.sessionSummary.lighting = "Golden Hour";
  } else if (text.includes("studio lighting")) {
    state.sessionSummary.lighting = "Stüdyo Işığı";
  } else if (text.includes("natural light")) {
    state.sessionSummary.lighting = "Doğal Işık";
  }

  // Lens
  const lensMatch = text.match(/\b(\d{2,3})mm\b/);
  if (lensMatch) {
    const fMatch = text.match(/f\/(\d+(?:\.\d+)?)/);
    state.sessionSummary.lens = `${lensMatch[1]}mm${fMatch ? `, f/${fMatch[1]}` : ''}`;
  }

  // Ortam
  if (text.includes("city") || text.includes("şehir")) {
    state.sessionSummary.environment = "Şehir";
  } else if (text.includes("forest") || text.includes("orman")) {
    state.sessionSummary.environment = "Orman";
  } else if (text.includes("studio")) {
    state.sessionSummary.environment = "Stüdyo";
  }

  // Özeti göster
  summaryContent.innerHTML = `
    ${state.sessionSummary.style ? `<div><span class="text-slate-400">Stil:</span> <span class="text-emerald-300">${state.sessionSummary.style}</span></div>` : ''}
    ${state.sessionSummary.lighting ? `<div><span class="text-slate-400">Işık:</span> <span class="text-emerald-300">${state.sessionSummary.lighting}</span></div>` : ''}
    ${state.sessionSummary.lens ? `<div><span class="text-slate-400">Lens:</span> <span class="text-emerald-300">${state.sessionSummary.lens}</span></div>` : ''}
    ${state.sessionSummary.environment ? `<div><span class="text-slate-400">Ortam:</span> <span class="text-emerald-300">${state.sessionSummary.environment}</span></div>` : ''}
  `;

  summaryCard.classList.remove("hidden");
}

async function generateVariations() {
  const btn = document.getElementById("generate-variations-btn");
  if (!btn || !state.currentPrompt) return;

  btn.disabled = true;
  btn.textContent = "Üretiliyor...";

  try {
    const csrf = getCsrfToken();
    const res = await fetch("/prompt-mentor/variations", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf,
        Accept: "application/json",
      },
      body: JSON.stringify({
        chat_id: chatId,
        base_prompt: state.currentPrompt,
      }),
    });

    if (!res.ok) {
      throw new Error("Variation generation failed");
    }

    const data = await res.json();
    showVariationsModal(data.variations || []);
  } catch (err) {
    console.error("Variation error:", err);
    alert("Varyasyon üretilirken bir hata oluştu.");
  } finally {
    btn.disabled = false;
    btn.textContent = "✨ Varyasyonlar";
  }
}

function showVariationsModal(variations) {
  const modal = document.getElementById("variations-modal");
  const content = document.getElementById("variations-content");
  const closeBtn = document.getElementById("close-variations-modal");

  if (!modal || !content) return;

  content.innerHTML = variations.map((variation, idx) => `
    <div class="bg-slate-900/60 rounded-xl border border-slate-800/80 p-4 space-y-3">
      <div class="flex items-center justify-between">
        <h4 class="text-xs font-semibold text-emerald-300">${variation.title || `Varyasyon ${idx + 1}`}</h4>
        <button
          type="button"
          class="copy-variation-btn text-[10px] px-2 py-1 rounded border border-emerald-500/60 text-emerald-300 hover:bg-emerald-500/10 transition"
          data-prompt="${escapeHtml(variation.prompt)}"
        >
          Kopyala
        </button>
      </div>
      <div class="text-[11px] text-slate-300 font-mono whitespace-pre-wrap bg-slate-950/60 p-2 rounded">
        ${escapeHtml(variation.prompt)}
      </div>
      ${variation.changes ? `<div class="text-[10px] text-slate-400">${escapeHtml(variation.changes)}</div>` : ''}
      ${variation.negativePrompt ? `<div class="text-[10px] text-slate-500 italic">Negatif: ${escapeHtml(variation.negativePrompt)}</div>` : ''}
    </div>
  `).join('');

  // Copy buttons
  content.querySelectorAll(".copy-variation-btn").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const prompt = btn.getAttribute("data-prompt");
      try {
        await navigator.clipboard.writeText(prompt);
        const original = btn.textContent;
        btn.textContent = "Kopyalandı!";
        setTimeout(() => {
          btn.textContent = original;
        }, 1500);
      } catch {
        alert("Kopyalama başarısız.");
      }
    });
  });

  modal.classList.remove("hidden");

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      modal.classList.add("hidden");
    }, { once: true });
  }

  // Close on outside click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("hidden");
    }
  }, { once: true });
}

function initVariationsButton() {
  const btn = document.getElementById("generate-variations-btn");
  if (btn) {
    btn.addEventListener("click", generateVariations);
  }
}

function setFinalState(isFinal, promptText, negativePromptText) {
  if (copyFinalBtn) {
    if (isFinal && promptText) {
      copyFinalBtn.classList.remove("hidden");
      const variationsBtn = document.getElementById("generate-variations-btn");
      if (variationsBtn) {
        variationsBtn.classList.remove("hidden");
      }
      if (finalHelperText) {
        finalHelperText.textContent =
          "Harika! Bu prompt profesyonel kullanım için hazır. Aşağıdan kopyalayıp Midjourney/DALL·E/Stable Diffusion'a yapıştırabilirsin.";
      }
    } else {
      copyFinalBtn.classList.add("hidden");
      const variationsBtn = document.getElementById("generate-variations-btn");
      if (variationsBtn) {
        variationsBtn.classList.add("hidden");
      }
      if (finalHelperText) {
        finalHelperText.textContent =
          "Gerçekçilik %90+ seviyesine ulaştığında kopyalanabilir final prompt burada hazır olacak.";
      }
    }
  }

  if (copyNegativeBtn) {
    if (isFinal && negativePromptText) {
      copyNegativeBtn.classList.remove("hidden");
    } else {
      copyNegativeBtn.classList.add("hidden");
    }
  }
}

function init() {
  if (!chatId) return;
  initIntro();
  bindSend();
  bindCopyButtons();
  updateStepper(state.stage);
  initChatManagement();
  initPresetTemplates();
  initStepperNavigation();
  initTabSwitching();
  initVariationsButton();
}

document.addEventListener("DOMContentLoaded", init);

