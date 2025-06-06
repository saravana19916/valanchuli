document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input.tamil-suggestion-input, textarea.tamil-suggestion-input')
    .forEach(el => bindTamilSuggestion(el));
});

function getLastWord(text) {
    const words = text.trim().split(/\s+/);
    return words.length > 0 ? words[words.length - 1] : "";
}

function showInputSuggestions(input, suggestionBox, suggestions) {
    suggestionBox.innerHTML = "";
    suggestions.forEach(suggestion => {
        const span = document.createElement("span");
        span.textContent = suggestion;
        span.style.marginRight = "10px";
        span.style.cursor = "pointer";
        span.style.color = "#005d67";
        span.style.textDecoration = "underline";

        span.addEventListener("click", () => {
            const words = input.value.trim().split(/\s+/);
            words.pop();
            words.push(suggestion);
            input.value = words.join(" ") + " ";
            suggestionBox.style.display = "none";
            input.focus();
        });

        suggestionBox.appendChild(span);
    });
    suggestionBox.style.display = "block";
}

function bindTamilSuggestion(input) {
    const inputId = input.id;
    const suggestionBox = document.querySelector(`.tamil-suggestion-box[data-suggestion-for="${inputId}"]`);
    if (!suggestionBox) return;

    input.removeEventListener("input", input._tamilInputListener);
    input.removeEventListener("blur", input._tamilBlurListener);

    const inputListener = () => {
        const text = input.value;
        const lastWord = getLastWord(text);
        if (lastWord.length > 1) {
            fetch(`https://inputtools.google.com/request?text=${encodeURIComponent(lastWord)}&itc=ta-t-i0-und&num=5`)
                .then(res => res.json())
                .then(data => {
                    if (data[0] === "SUCCESS") {
                        const suggestions = data[1][0][1];
                        showInputSuggestions(input, suggestionBox, suggestions);
                    } else {
                        suggestionBox.style.display = "none";
                    }
                })
                .catch(() => suggestionBox.style.display = "none");
        } else {
            suggestionBox.style.display = "none";
        }
    };

    const blurListener = () => setTimeout(() => suggestionBox.style.display = "none", 200);

    input._tamilInputListener = inputListener;
    input._tamilBlurListener = blurListener;

    input.addEventListener("input", inputListener);
    input.addEventListener("blur", blurListener);
}