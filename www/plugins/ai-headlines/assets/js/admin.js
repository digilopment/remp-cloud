jQuery(document).ready(function ($) {
    const postId = $('#post_ID').val();
    const nonce = $('#ai-headlines').data('nonce');

    // Funkcia na vykreslenie nadpisov
    function renderTitles(titles)
    {
        if (titles.length) {
            let html = '<ul style="cursor:pointer;">';
            titles.forEach(title => {
                html += '<li class="ai-title-item">' + title + '</li>';
            });
            html += '</ul>';
            $('#ai-headlines-output').html(html);
        }
    }

    // Hneď načítať existujúce návrhy
    /*$.post(AiHeadlines.ajax_url, {
        action: 'ai_headlines',
        post_id: postId,
        nonce: nonce
    }, function(response) {
        if(response.success && response.data.titles.length) {
            renderTitles(response.data.titles);
        }
    });*/

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

        $.post(AiHeadlines.ajax_url, {
            action: 'ai_headlines',
            post_id: postId,
            nonce: nonce,
            force: force
        }, function (response) {
            $('#ai-headlines-output').html('Generujem nadpisy...');

            if (response.success && response.data.titles.length) {
                renderTitles(response.data.titles);
            } else if (!response.success) {
                $('#ai-headlines-output').html('Chyba pri generovaní nadpisov.');
            }
        });
    });
});
