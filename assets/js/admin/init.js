// NOT IN USE
const daterangepickerSettings = {
  locale: {
    format: 'YYYY-MM-DD',
    // format: 'MMM DD, YYYY',
    separator: ' - ',
    dateRange: "{{ 'datetime.date-range'|trans }}",
    customRangeLabel: "{{ 'datetime.custom-range'|trans }}",
    applyLabel: "{{ 'generic.apply'|trans }}",
    cancelLabel: "{{ 'generic.delete'|trans }}",
    daysOfWeek: [
      "{{ 'datetime.sun'|trans }}",
      "{{ 'datetime.mon'|trans }}",
      "{{ 'datetime.tue'|trans }}",
      "{{ 'datetime.wed'|trans }}",
      "{{ 'datetime.thu'|trans }}",
      "{{ 'datetime.fri'|trans }}",
      "{{ 'datetime.sat'|trans }}",
    ],
    monthNames: [
      "{{ 'datetime.january'|trans }}",
      "{{ 'datetime.february'|trans }}",
      "{{ 'datetime.march'|trans }}",
      "{{ 'datetime.april'|trans }}",
      "{{ 'datetime.mayy'|trans }}",
      "{{ 'datetime.june'|trans }}",
      "{{ 'datetime.july'|trans }}",
      "{{ 'datetime.august'|trans }}",
      "{{ 'datetime.september'|trans }}",
      "{{ 'datetime.october'|trans }}",
      "{{ 'datetime.november'|trans }}",
      "{{ 'datetime.december'|trans }}",
    ],
    firstDay: 1,
  },

  buttonClasses: 'btn',
  applyClass: 'btn-primary',
  cancelClass: 'btn-secondary',

}