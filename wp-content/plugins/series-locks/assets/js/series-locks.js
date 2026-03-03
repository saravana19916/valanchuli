document.addEventListener('DOMContentLoaded', function () {

    const openBtn = document.getElementById('open-lock-modal');
    const modal = document.getElementById('lock-modal');
    const closeBtn = document.getElementById('lock_cancel_btn');
    const form = document.getElementById('lockForm');

    if (!openBtn || !modal) return;

    openBtn.addEventListener('click', () => {
        modal.style.display = 'block';
    });

    closeBtn?.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    form?.addEventListener('submit', function (e) {
        e.preventDefault();

        const type = document.getElementById('lock_type').value;
        const from = document.getElementById('lock_from').value;
        const to = document.getElementById('lock_to').value;
        const editIndex = document.getElementById('edit_index').value;

        const adsTimeSec = document.getElementById('ads_time_sec').value;
        let adsContent = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('ads_content')) {
            adsContent = tinymce.get('ads_content').getContent();
        } else {
            adsContent = document.getElementById('ads_content').value;
        }

        const postIds = Array.from(
            document.querySelectorAll('input[name="post_ids[]"]:checked')
        ).map(el => el.value);

        const params = new URLSearchParams({
            action: 'add_series_lock',
            nonce: seriesLocks.nonce,
            type,
            from,
            to
        });

        if (editIndex !== '') {
            params.append('edit_index', editIndex);
        }

        postIds.forEach(id => params.append('post_ids[]', id));
        params.append('ads_time_sec', adsTimeSec);
        params.append('ads_content', adsContent);

        fetch(seriesLocks.ajaxurl, {
            method: 'POST',
            body: params
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Lock updated successfully!');
                location.reload();
            } else {
                alert(data.data || 'Something went wrong');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Server error');
        });
    });

});
