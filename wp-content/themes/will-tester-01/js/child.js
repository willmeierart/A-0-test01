jQuery( document ).ready(function() {
  console.log('js working')
  const recursIframe = jQuery(`<iframe src=${window.location}></iframe>`)
  recursIframe.appendTo(jQuery('.az-popup-open'))
  jQuery('.az-popup').click(e=>console.log('clicked'))
})
