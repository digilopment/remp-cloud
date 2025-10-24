jQuery(document).ready(function ($) {
    const postId = $('#post_ID').val();
    const nonce = $('#ai-headlines').data('nonce');

    function renderResponse(titles, topic) {
        let html = '';

        if (topic) {
            html += '<ul>';
            html += '<li class="ai-main-topic" style="background-color:orange; font-weight:bold;">' + topic + '</li>';
            html += '</ul>';
        }

        if (titles.length) {
            html += '<ul style="cursor:pointer;">';
            titles.forEach(title => {
                html += '<li class="ai-title-item">' + title + '</li>';
            });
            html += '</ul>';
        }

        $('#ai-headlines-output').html(html);
    }


    // Hneď načítať existujúce návrhy
    function loadHeadlines() {
        $.post(AiHeadlines.ajax_url, {
            action: 'ai_headlines',
            post_id: postId,
            nonce: nonce
        }, function (response) {
            if (response.success) {
                renderResponse(response.data.titles, response.data.topic);
            }
        });
    }

    // Kliknutie na title nastaví post title a uloží cez AJAX
    $(document).on('click', '.ai-title-item', function () {
        const selectedTitle = $(this).text();

        $.post(AiHeadlines.ajax_url, {
            action: 'ai_set_title',
            post_id: postId,
            title: selectedTitle,
            nonce: nonce
        }, function (resp) {
            if (resp.success) {
                location.reload();
            }
        });
    });

    // Generovanie nových AI headlines po kliknutí na tlačidlo
    $('#ai-headlines').on('click', function () {
        const force = $('#ai-headlines-force').is(':checked') ? 1 : 0;

        $('#ai-headlines-output').html('Generujem nadpisy...');

        $.post(AiHeadlines.ajax_url, {
            action: 'ai_headlines',
            post_id: postId,
            nonce: nonce,
            force: force
        }, function (response) {
            if (response.success) {
                $('#ai-headlines-output').empty();
                renderResponse(response.data.titles, response.data.topic);
            } else {
                $('#ai-headlines-output').html('Chyba pri generovaní nadpisov.');
            }
        });
    });
    //loadHeadlines();
});
