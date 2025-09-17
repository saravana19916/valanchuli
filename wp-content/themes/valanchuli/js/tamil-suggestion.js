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
            const cursorPos = input.selectionStart;
            const text = input.value;

            // Find the start of the current word
            let start = cursorPos;
            while (start > 0 && !/\s/.test(text[start - 1])) {
                start--;
            }

            // Find the end of the current word
            let end = cursorPos;
            while (end < text.length && !/\s/.test(text[end])) {
                end++;
            }

            // Replace the current word with the selected suggestion
            const newText = text.substring(0, start) + suggestion + text.substring(end);

            input.value = newText;

            // Set cursor position after the replaced word + a space
            const newCursorPos = start + suggestion.length + 1;
            input.setSelectionRange(newCursorPos, newCursorPos);

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
        const cursorPos = input.selectionStart;

        // Get the current word near the cursor instead of the last word
        const currentWord = getWordAtCursor(text, cursorPos);

        if (currentWord.length > 1) {
            fetch(`https://inputtools.google.com/request?text=${encodeURIComponent(currentWord)}&itc=ta-t-i0-und&num=5`)
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

// ✅ New function to get the word nearest to the cursor
function getWordAtCursor(text, cursorPos) {
    const left = text.slice(0, cursorPos).split(/\s+/);
    const right = text.slice(cursorPos).split(/\s+/);

    // Last word from left + first word from right
    const leftWord = left[left.length - 1] || "";
    const rightWord = right[0] || "";

    // If cursor is inside a word, return leftWord
    return leftWord || rightWord;
}