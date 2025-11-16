/**
 * @file
 * Support "deep linking" to Bootstrap tabs.
 */

(Drupal => {
  window.addEventListener('load', function () {
    let url = location.href.replace(/\/$/, '');
    if (location.hash) {
      const params = url.split('#');
      const anchor = params[1];
      const anchoredTab = document.querySelector('#bootstrap-horizontal-tabs a[href="#' + anchor + '"]');
      if (anchoredTab) {
        anchoredTab.click();
        setTimeout(() => {
          anchoredTab.scrollIntoView(true);
        }, 750);
      }
    }
  })
})(Drupal);
