(function ($) {
  function initRepeater($repeater) {
    var $rows = $repeater.find('.mno-pm-repeater__rows');
    var template = $repeater.find('.mno-pm-repeater__template').html();
    var nextIndex = parseInt($repeater.attr('data-next-index'), 10);

    if (isNaN(nextIndex)) {
      nextIndex = $rows.children().length;
    }

    $repeater.on('click', '.mno-pm-repeater__add', function (event) {
      event.preventDefault();
      if (!template) {
        return;
      }
      var html = template;

      if (html.indexOf('__index__') !== -1) {
        html = html.replace(/__index__/g, nextIndex);
        nextIndex += 1;
        $repeater.attr('data-next-index', nextIndex);
      }

      $rows.append(html);
    });

    $repeater.on('click', '.mno-pm-repeater__remove', function (event) {
      event.preventDefault();
      $(this).closest('.mno-pm-repeater__row').remove();
    });

    if ($rows.length) {
      $rows.sortable({
        handle: '.mno-pm-repeater__handle',
        placeholder: 'mno-pm-repeater__placeholder',
        forcePlaceholderSize: true
      });
    }
  }

  function initGallery() {
    var $list = $('#mno-pm-gallery-list');
    if (!$list.length) {
      return;
    }

    var template = $('#mno-pm-gallery-template').html();
    var frame;

    $list.sortable({
      handle: '.mno-pm-gallery__handle',
      placeholder: 'mno-pm-gallery__placeholder',
      forcePlaceholderSize: true
    });

    $('#mno-pm-add-gallery').on('click', function (event) {
      event.preventDefault();

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: $(this).text(),
        button: { text: $(this).text() },
        multiple: true
      });

      frame.on('select', function () {
        var selection = frame.state().get('selection');
        selection.each(function (attachment) {
          var data = attachment.toJSON();
          var preview = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
          var alt = data.alt || data.title || data.filename || '';
          var imageMarkup = '<img src="' + preview + '" alt="' + alt.replace(/"/g, '&quot;') + '" />';
          var html = template.replace(/{{id}}/g, data.id).replace('{{image}}', imageMarkup);
          $list.append(html);
        });
      });

      frame.open();
    });

    $list.on('click', '.mno-pm-gallery__remove', function (event) {
      event.preventDefault();
      $(this).closest('.mno-pm-gallery__item').remove();
    });
  }

  function initSingleMedia() {
    $('.mno-pm-media').each(function () {
      var $container = $(this);
      var $preview = $container.find('.mno-pm-media__preview');
      var $input = $container.find('input[type="hidden"]');
      var $select = $container.find('.mno-pm-media__select');
      var $remove = $container.find('.mno-pm-media__remove');
      var placeholder = $container.data('placeholder') || '';
      var frame;

      $select.on('click', function (event) {
        event.preventDefault();

        if (frame) {
          frame.open();
          return;
        }

        frame = wp.media({
          title: $(this).text(),
          button: { text: $(this).text() },
          multiple: false
        });

        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          var preview = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
          var alt = attachment.alt || attachment.title || attachment.filename || '';
          $preview.html('<img src="' + preview + '" alt="' + alt.replace(/"/g, '&quot;') + '" />');
          $input.val(attachment.id);
          $container.addClass('has-image');
        });

        frame.open();
      });

      $remove.on('click', function (event) {
        event.preventDefault();
        $preview.text(placeholder);
        $input.val('');
        $container.removeClass('has-image');
      });
    });
  }

  $(function () {
    $('.mno-pm-repeater').each(function () {
      initRepeater($(this));
    });

    initGallery();
    initSingleMedia();
  });
})(jQuery);