window.onload = function() {
    var rows = document.getElementsByName('messages'),
        subject = document.getElementById('message-subject'),
        uri;

    for (var i=0,len=rows.length; i<len; i++){
        rows[i].onclick = function(){
            uri = this.getAttribute('data-originator');
            window.location = 'runmodule.php?module=mail&op=view&id=' + uri;
        };
    }

    subject.addEventListener('click', editTitle, false);
    subject.addEventListener('touchend', editTitle);
}

function editTitle(event) {
    var form = document.getElementById('message-subject-form'),
        subject = document.getElementById('message-subject');
    console.log(subject.style);
    subject.style.display = 'none';
    form.style.display = 'block';
    return false;
}