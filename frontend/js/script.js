document.addEventListener('DOMContentLoaded', function () {
    function escapeHtml(text) {
        var element = document.createElement('div');
        element.textContent = text;
        return element.innerHTML;
    }

    function renderInline(text) {
        text = text.replace(/\[([^\]]+)\]\(([^\s)]+)\)/g, function (match, label, url) {
            if (!/^https?:\/\//i.test(url)) {
                return label + ' (' + url + ')';
            }

            var safeUrl = url.replace(/\*/g, '&#42;').replace(/\+/g, '&#43;');
            return '<a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer">' + label + '</a>';
        });
        text = text.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\+\+([^+\n]+)\+\+/g, '<u>$1</u>');
        text = text.replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1<em>$2</em>');
        return text;
    }

    function renderMarkdown(value) {
        var lines = escapeHtml(value).replace(/\r\n|\r/g, '\n').split('\n');
        var html = '';
        var paragraph = [];
        var listType = '';

        function closeParagraph() {
            if (paragraph.length > 0) {
                html += '<p>' + renderInline(paragraph.join('<br>')) + '</p>';
                paragraph = [];
            }
        }

        function closeList() {
            if (listType !== '') {
                html += '</' + listType + '>';
                listType = '';
            }
        }

        for (var index = 0; index < lines.length; index++) {
            var line = lines[index];
            var heading = line.match(/^(#{1,3})\s+(.+)$/);
            var unordered = line.match(/^[-*]\s+(.+)$/);
            var ordered = line.match(/^\d+\.\s+(.+)$/);

            if (line.trim() === '') {
                closeParagraph();
                closeList();
            } else if (heading) {
                closeParagraph();
                closeList();
                html += '<h' + heading[1].length + '>' + renderInline(heading[2]) + '</h' + heading[1].length + '>';
            } else if (unordered) {
                closeParagraph();

                if (listType !== 'ul') {
                    closeList();
                    html += '<ul>';
                    listType = 'ul';
                }

                html += '<li>' + renderInline(unordered[1]) + '</li>';
            } else if (ordered) {
                closeParagraph();

                if (listType !== 'ol') {
                    closeList();
                    html += '<ol>';
                    listType = 'ol';
                }

                html += '<li>' + renderInline(ordered[1]) + '</li>';
            } else {
                closeList();
                paragraph.push(line);
            }
        }

        closeParagraph();
        closeList();
        return html;
    }

    var blogForm = document.querySelector('.blog-form');
    var contentField = document.getElementById('content');
    var contentCount = document.getElementById('content-count');
    var preview = document.getElementById('markdown-preview');

    function updateEditor() {
        if (!contentField) {
            return;
        }

        if (contentCount) {
            contentCount.textContent = contentField.value.length;
        }

        if (preview) {
            if (contentField.value.trim() === '') {
                preview.innerHTML = '<p class="preview-placeholder">Your formatted preview will appear here.</p>';
            } else {
                preview.innerHTML = renderMarkdown(contentField.value);
            }
        }
    }

    if (contentField) {
        contentField.addEventListener('input', updateEditor);
        updateEditor();
    }

    var toolbarButtons = document.querySelectorAll('[data-markdown]');

    for (var toolbarIndex = 0; toolbarIndex < toolbarButtons.length; toolbarIndex++) {
        toolbarButtons[toolbarIndex].addEventListener('click', function () {
            var action = this.getAttribute('data-markdown');
            var start = contentField.selectionStart;
            var end = contentField.selectionEnd;
            var selected = contentField.value.substring(start, end);
            var before = contentField.value.substring(0, start);
            var after = contentField.value.substring(end);
            var replacement = selected;

            if (action === 'bold') {
                replacement = '**' + (selected || 'bold text') + '**';
            } else if (action === 'italic') {
                replacement = '*' + (selected || 'italic text') + '*';
            } else if (action === 'underline') {
                replacement = '++' + (selected || 'underlined text') + '++';
            } else if (action === 'heading') {
                replacement = '# ' + (selected || 'Heading');
            } else if (action === 'unordered-list') {
                replacement = '- ' + (selected || 'List item').replace(/\n/g, '\n- ');
            } else if (action === 'ordered-list') {
                var items = (selected || 'List item').split('\n');
                replacement = '';

                for (var itemIndex = 0; itemIndex < items.length; itemIndex++) {
                    replacement += (itemIndex + 1) + '. ' + items[itemIndex];

                    if (itemIndex < items.length - 1) {
                        replacement += '\n';
                    }
                }
            } else if (action === 'link') {
                var url = window.prompt('Enter a full link beginning with http:// or https://', 'https://');

                if (url === null) {
                    return;
                }

                if (!/^https?:\/\//i.test(url)) {
                    window.alert('Please enter a link beginning with http:// or https://');
                    return;
                }

                replacement = '[' + (selected || 'link text') + '](' + url + ')';
            }

            contentField.value = before + replacement + after;
            contentField.focus();
            contentField.setSelectionRange(start + replacement.length, start + replacement.length);
            updateEditor();
        });
    }

    if (blogForm) {
        blogForm.addEventListener('submit', function (event) {
            var title = document.getElementById('title').value.trim();
            var content = contentField.value.trim();
            var formError = document.getElementById('form-error');

            if (title === '' || content === '') {
                event.preventDefault();
                formError.textContent = 'Title and content are required.';
                formError.style.display = 'block';
            } else if (content.length > 3000) {
                event.preventDefault();
                formError.textContent = 'Content must be 3000 characters or fewer.';
                formError.style.display = 'block';
            } else {
                formError.textContent = '';
                formError.style.display = 'none';
            }
        });
    }

    var loginForm = document.querySelector('.login-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value;
            var loginError = document.getElementById('login-error');
            var message = '';

            if (username === '' || password === '') {
                message = 'Username and password are required.';
            } else if (username.length > 50) {
                message = 'Username must be 50 characters or fewer.';
            } else if (password.length < 6) {
                message = 'Password must be at least 6 characters.';
            }

            if (message !== '') {
                event.preventDefault();
                loginError.textContent = message;
                loginError.style.display = 'block';
            } else {
                loginError.textContent = '';
                loginError.style.display = 'none';
            }
        });
    }

    var commentForm = document.querySelector('.comment-form');
    var commentField = document.getElementById('comment-content');
    var commentCount = document.getElementById('comment-count');

    if (commentField && commentCount) {
        commentCount.textContent = commentField.value.length;
        commentField.addEventListener('input', function () {
            commentCount.textContent = commentField.value.length;
        });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', function (event) {
            var commentError = document.getElementById('comment-error');
            var value = commentField.value.trim();

            if (value === '') {
                event.preventDefault();
                commentError.textContent = 'Comment is required.';
                commentError.style.display = 'block';
            } else if (value.length > 1000) {
                event.preventDefault();
                commentError.textContent = 'Comment must be 1000 characters or fewer.';
                commentError.style.display = 'block';
            } else {
                commentError.textContent = '';
                commentError.style.display = 'none';
            }
        });
    }

    var deleteForms = document.querySelectorAll('.delete-form');

    for (var index = 0; index < deleteForms.length; index++) {
        deleteForms[index].addEventListener('submit', function (event) {
            if (!window.confirm('Are you sure you want to delete this blog?')) {
                event.preventDefault();
            }
        });
    }

    var commentDeleteForms = document.querySelectorAll('.comment-delete-form');

    for (var commentIndex = 0; commentIndex < commentDeleteForms.length; commentIndex++) {
        commentDeleteForms[commentIndex].addEventListener('submit', function (event) {
            if (!window.confirm('Are you sure you want to delete this comment?')) {
                event.preventDefault();
            }
        });
    }
});
