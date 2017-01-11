window.onload = () => {
    var messages = document.getElementsByName('messages'),
        subject = document.getElementById('message-subject'),
        replyForm = document.getElementById('message-reply-form'),
        users = document.getElementsByName('users')

    if (subject) {
        subject.addEventListener('click', editTitle);
        subject.addEventListener('touchend', editTitle);
    }
    if (replyForm) {
        replyContainer = document.getElementById('message-reply')
        replyForm.onmouseover = () => {
            replyContainer.style.backgroundColor = '#111';
        }
        replyForm.onmouseout = () => {
            if (document.activeElement.name != 'reply') {
                replyContainer.style.backgroundColor = '#222';
            }
        }
        replyForm.onfocus = () => {
            replyContainer.style.backgroundColor = '#111';
        }
        replyForm.onblur = () => {
            replyContainer.style.backgroundColor = '#222';
        }
    }
    for (var i = 0, len = messages.length; i < len; i++) {
        messages[i].onclick = function () {
            origin = this.getAttribute('data-originator');
            window.location = 'runmodule.php?module=mail&op=view&id=' + origin + '#last';
        };
    }
    for (var i = 0, len = users.length; i < len; i++) {
        users[i].onclick = function () {
            userSelect = document.getElementById('message-to');
            composeContainer = document.getElementById('new-message');
            to = document.getElementById('to');
            userSelect.innerHTML = this.getAttribute('data-name') + '</span>';
            userSelect.style.paddingBottom = '0px';
            composeContainer.style.display = 'block';
            to.value = this.getAttribute('data-acctid');
        }
    }
}

function editTitle() {
    var form = document.getElementById('message-subject-form'),
        subject = document.getElementById('message-subject');
    subject.style.display = 'none';
    form.style.display = 'block';
    return false;
}