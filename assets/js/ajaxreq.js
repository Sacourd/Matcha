var xhr = new XMLHttpRequest();

    if (document.getElementsByClassName('btn')[0])
        var btnLike = document.getElementsByClassName('btn')[0];
    if (document.getElementsByClassName('btn')[1])
        var btnBlock = document.getElementsByClassName('btn')[1];
    if (document.getElementsByClassName('btn')[2])
        var btnAlert = document.getElementsByClassName('btn')[2];

    function request(url) {
        xhr.open('GET', url);
        xhr.send();
    }

    function like(id, type) {
        let score = document.getElementById('score');
        if (type === 'like') {
            var newAction = 'dislike';
            score.innerHTML = parseInt(score.innerHTML) + 100;
            btnLike.className = 'btn btn-danger border rounded-circle border-danger';
        }
        else {
            var newAction = 'like';
            score.innerHTML = parseInt(score.innerHTML) - 100;
            btnLike.className = 'btn btn-outline-danger border rounded-circle border-danger';
        }
        let url = window.location.href + '&' + type + '=' + id;
        btnLike.setAttribute( "onClick", "like("+id+", '"+newAction+"');" );
        request(url);
    }

    function block(id) {
        btnBlock.setAttribute( "onClick", "unblock("+id+");" );
        btnBlock.className = 'btn btn-danger border rounded-circle border-danger';
        let url = window.location.href + '&block=' + id;
        request(url);
    } 

    function unblock(id) {
        if (btnBlock !== undefined) {
            btnBlock.setAttribute( "onClick", "block("+id+");" );
            btnBlock.className = 'btn btn-outline-danger border rounded-circle border-danger';
        }
        let url = window.location.href + '&unblock=' + id;
        request(url);
    }

    function alertUser(id, type) {
        if (type == 'alert') {
            var newAction = 'unalert';
            btnAlert.setAttribute( "onClick", "alertUser("+id+", '"+newAction+"');" );
            btnAlert.className = 'btn btn-danger border rounded-circle border-danger';
        }
        else {
            var newAction = 'alert';
            btnAlert.setAttribute( "onClick", "alertUser("+id+", '"+newAction+"');" );
            btnAlert.className = 'btn btn-outline-danger border rounded-circle border-danger';
        }
        let url = window.location.href + '&' + 'alert' + '=' + type;
        request(url);
    }

    function removeLine(remove) {
        let tr = document.getElementById(remove);
        unblock(remove.substr(5));
        tr.style = 'display:none';
    }