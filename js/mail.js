var replyContainer, replyForm;


window.onload = () => {
    var rows = document.getElementsByName('messages'),
        subject = document.getElementById('message-subject'),
        uri;


    replyContainer = document.getElementById('message-reply');
    replyForm = document.getElementById('message-reply-form');
    for (var i=0,len=rows.length; i<len; i++){
        rows[i].onclick = function(){
            uri = this.getAttribute('data-originator');
            window.location = 'runmodule.php?module=mail&op=view&id=' + uri + '#last';
        };
    }

    subject.addEventListener('click', editTitle);
    subject.addEventListener('touchend', editTitle);
    /*replyForm.addEventListener('focus', focusForm);
    replyForm.addEventListener('blur', blurForm);*/

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

function editTitle() {
    var form = document.getElementById('message-subject-form'),
        subject = document.getElementById('message-subject');
    console.log(subject.style);
    subject.style.display = 'none';
    form.style.display = 'block';
    return false;
}

function focusForm() {
    replyContainer.style.backgroundColor = '#111';
    return false;
}
function blurForm() {
    replyContainer.style.backgroundColor = '#222';
    return false;
}