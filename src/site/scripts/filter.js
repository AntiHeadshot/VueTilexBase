let monthFormater = new Intl.DateTimeFormat('default', { month: 'long' });

let dateFormater = new Intl.DateTimeFormat('default', { day: 'numeric', month: 'long', year: 'numeric' });

let dateTimeMonthFormater = new Intl.DateTimeFormat('default', { day: 'numeric', month: 'short', year: 'numeric', 'hour': 'numeric', 'minute': 'numeric' });
let dateTimeFormater = new Intl.DateTimeFormat('default', { day: 'numeric', month: 'numeric', year: 'numeric', 'hour': 'numeric', 'minute': 'numeric' });
let timeFormater = new Intl.DateTimeFormat('default', { 'hour': 'numeric', 'minute': 'numeric' });


Vue.filter('month', function(value) {
    if (value != null)
        return monthFormater.format(new Date(2000, value));
    return value;
});

Vue.filter('date', function(value) {
    if (value != null)
        return dateFormater.format(value);
    return value;
});

Vue.filter('timeStamp', function(value) {
    if (value != null)
        return value / (60 * 60);
    return value;
});

Vue.filter('time', function(value) {
    if (value != null)
        return timeFormater.format(value);
    return value;
});

Vue.filter('dateTime', function(value) {
    if (value != null)
        return dateTimeFormater.format(value);
    return value;
});

Vue.filter('dateTimeMonth', function(value) {
    if (value != null)
        return dateTimeMonthFormater.format(value);
    return value;
});