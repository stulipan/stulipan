// Alert

showSuccessAt(position) {
  if (this.hasError) {
    this.hideError();
  }
  this.hideWarning();
  if (!this.hasSuccess) {
    position.after(AlertHtml.pre.success + AlertMessages.SUCCESS_PRODUCT_WAS_ADDED + AlertHtml.post);
    this.hasSuccess = true;
  }
  position[0].scrollIntoView(scrollUp);
}
showErrorAt(position, msg) {
  this.hideSuccess();
  this.hideWarning();
  this.hideError();
  position.after(AlertHtml.pre.danger + msg + AlertHtml.post);
  position[0].scrollIntoView(scrollUp);
  this.hasError = true;
}
showWarningAt(position, msg) {
  this.hideSuccess();
  this.hideWarning();
  this.hideError();
  position.after(AlertHtml.pre.warning + msg + AlertHtml.post);
  position[0].scrollIntoView(scrollUp);
}

hideSuccess() {
  if (this.hasSuccess) {
    this.wrapper.find(Wrapper.ALERT_MESSAGE).replaceWith('');
    this.hasSuccess = false;
  }
}
hideWarning() {
  this.wrapper.find(Wrapper.ALERT_MESSAGE).replaceWith('');
}
hideError() {
  if (this.hasError) {
    this.wrapper.find(Wrapper.ALERT_MESSAGE).replaceWith('');
    this.hasError = false;
  }
}

// End Alert



submitRecipientForm(e) {
  e.preventDefault();
  // let $el = $(e.currentTarget);
  if (this._proceed) { this._proceed = false; return; }

  let $form = $(e.currentTarget);
  let $el = $form.find('.JS--Button-submit');
  this.showOverlay($el, Event.CLICK);  // add overlay to the button, not the form!

  let $wrapperBody = $form.closest(Wrapper.RECIPIENT_BODY);
  $.ajax({
    url: $form.attr('action'),
    method: 'POST',
    data: $form.serialize(),
    context: this,
  }).done(function(data) {
    $('[data-eval="getRecipients"]').trigger('click');

    $wrapperBody.html(data); // equiv: $form.replaceWith(data);
    this.recipient.isFormDisplayed = false;
    this.recipient.hideError();
    this._proceed = false;
  }).fail(function(jqXHR) {
    $wrapperBody.html(jqXHR.responseText); // equiv: $form.replaceWith(jqXHR.responseText);
    this.hideOverlay($el);
  });
}

$(Wrapper.GLOBAL).on('click', '[data-eval="getRecipient"]', this.getRecipient.bind(this))  // added manually, mert a contruct lefutasakor meg nincs a HTML-ben!
getRecipient(e) {
  e.preventDefault();
  let $el = $(e.currentTarget);
  if (this._proceed) { this._proceed = false; return; }
  this.showOverlay($el, Event.CLICK);

  let url = $el.data('url');
  let $wrapper = $el.closest(Wrapper.RECIPIENT);
  let $wrapperBody = $wrapper.find(Wrapper.RECIPIENT_BODY);
  $.ajax({
    url: url,
    method: 'GET',
    context: this,
  }).done(function(data) {
    $wrapperBody.html(data);
    this.recipient.isFormDisplayed = false;
    this.recipient.hideError();
  }).fail(function(jqXHR) {
    this.hideOverlay($el);
    this.recipient.showErrorAt($wrapper.find(Wrapper.ALERT));
  });
}

submitSenderForm(e) {
  e.preventDefault();
  // let $el = $(e.currentTarget);
  if (this._proceed) { this._proceed = false; return; }

  let $form = $(e.currentTarget);
  let $el = $form.find('.JS--Button-submit');
  this.showOverlay($el, Event.CLICK);

  let $wrapperBody = $el.closest(Wrapper.SENDER_BODY);
  this.sender.hideError();  // elrejti az esetleges uzenetet, ha volt
  // alert.sender.hideError($wrapper);  // elrejti az esetleges uzenetet, ha volt
  $.ajax({
    url: $form.attr('action'),
    method: 'POST',
    data: $form.serialize(),
    context: this,
  }).done(function(data) {
    $('[data-eval="refreshSenderList"]').trigger('click');
    $wrapperBody.html(data);
    this.sender.isFormDisplayed = false;
    this.sender.hideError($wrapper);
    this._proceed = false;
  }).fail(function(jqXHR) {
    $wrapperBody.html(jqXHR.responseText) // equiv: $form.replaceWith(jqXHR.responseText);
    this.hideOverlay($el);
  });
}

$(Wrapper.GLOBAL).on('click', '[data-eval="getSender"]', this.getSender.bind(this))  // added manually, mert a contruct lefutasakor meg nincs a HTML-ben!
getSender(e) {
  e.preventDefault();
  let $el = $(e.currentTarget);
  if (this._proceed) { this._proceed = false; return; }
  this.showOverlay($el, Event.CLICK);

  let url = $el.data('url');
  let $wrapper = $el.closest(Wrapper.SENDER);
  let $wrapperBody = $wrapper.find(Wrapper.SENDER_BODY);
  $.ajax({
    url: url,
    method: 'GET',
    context: this
  }).done(function(data) {
    $wrapperBody.html(data);
    this.sender.isFormDisplayed = false;
    this.sender.hideError();
  }).fail(function(jqXHR) {
    this.hideOverlay($el);
    this.sender.showErrorAt($wrapper.find(Wrapper.ALERT));
  });
}

submitShippingAndPaymentForm(e) {
  if (this.hasNoErrors()) {
    e.preventDefault();
    let $el = $(e.currentTarget);
    if (this._proceed) { this._proceed = false; return; }
    this.showOverlay($el, Event.CLICK);
    let url = $el.data('url');
    let $wrapper = $el.closest(Wrapper.GLOBAL);
    let $shipAndPayForm = $el.find('.JS--shipAndPayForm');
    let $registrationForm = $wrapper.find('.JS--registrationForm');
    // console.log($shipAndPayForm);

    // if ($wrapper.find(Wrapper.SENDER_BODY).find('.JS--item').length < 1) {
    //   this.sender.isFormDisplayed = true;
    // }
    if (this.sender.isFormDisplayed) {
      if (!this.sender.hasError) {
        this.sender.showErrorAt($wrapper.find(Wrapper.SENDER).find(Wrapper.ALERT), Error.SENDER_DATA_NOT_SAVED);
        this.sender.hasError = true;
      }
      this.hideOverlay($el);
      $wrapper.find(Wrapper.SENDER)[0].scrollIntoView(scrollUp);
    } else {
      // elposztolja a regisztracios formot
      $.ajax({
        url: $registrationForm.attr('action'),
        method: 'POST',
        data: $registrationForm.serialize(),
        context: this
      }).done(function(data) {
        // console.log(data);
        // nem kell csinaljak semmit, mert sikeres regisztracioval be is lepteti a usert.
        // $registrationForm.replaceWith(data);

        // Ezutan: elposztolja a shipAndPay formot
        $.ajax({
          url: $shipAndPayForm.attr('action'),
          method: 'POST',
          data: $shipAndPayForm.serialize(),
          context: this
        }).done(function(data) {
          // console.log($shipAndPayForm.attr('action'));
          window.location.href = url;
        }).fail(function(jqXHR) {
          $shipAndPayForm.replaceWith(jqXHR.responseText);
          this.hideOverlay($el);
        });
      }).fail(function(jqXHR) {
        $registrationForm.replaceWith(jqXHR.responseText);
        this.hideOverlay($el);
      });
    }
  }
}


