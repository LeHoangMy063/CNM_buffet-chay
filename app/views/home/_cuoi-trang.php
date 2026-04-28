<div class="toast-admin" id="toast"></div>
</div><!-- .content -->
</div><!-- .main -->
<script>
function showToast(msg, error) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast-admin show' + (error ? ' error' : '');
    setTimeout(function() { t.className = 'toast-admin'; }, 3500);
}
function apiPost(url, data) {
    var fd = new FormData();
    for (var k in data) {
        if (data.hasOwnProperty(k)) fd.append(k, data[k]);
    }
    return fetch(url, { method: 'POST', body: fd }).then(function(res) { return res.json(); });
}
</script>
</body>
</html>
