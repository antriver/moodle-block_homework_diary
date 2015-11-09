function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

/**
 * Show all assigned dates when the list has been limited to 2
 */
$(document).on('click', '.showAllHomeworkDates', function (e) {
    e.preventDefault();

    // Get the hidden json
    var item = $(this).closest('.homework');
    var json = item.find('.assignedDatesJSON').text();
    var dates = $.parseJSON(json);

    var duedate = item.attr('data-duedate');

    var ul = '<ul class="assignedDates">';

    ul += '<li><strong>To Do On</strong></li>';

    for (var i = 0; i < dates.length; i++) {
        var date = dates[i];
        ul += '<li><span class="label label-info">' + formatDate('D M jS', new Date(date)) + '</span></li>';
    }
    ul += '<li><i class="fa fa-arrow-down"></i></li>';
    ul += '<li>';

    if (item.hasClass('past')) {
        ul += '<span class="label label-important">';
        ul += '<i class="fa fa-bell"></i> <strong>Was due on</strong> ' + formatDate('D M jS Y', new Date(duedate));
        ul += '</span>';
    } else {
        ul += '<span class="label label-info">';
        ul += '<i class="fa fa-bell"></i> <strong>Due on</strong> ' + formatDate('D M jS Y', new Date(duedate));
        ul += '</span>';
    }

    ul += '</li>';

    ul += '</ul>';

    item.find('.dates').html(ul);

});


$(document).on('click', '.approveHomeworkButton', function (e) {
    e.preventDefault();

    var btn = $(this);
    if (btn.hasClass('loading')) {
        return false;
    }

    var $hw = btn.closest('.homework');
    var id = $hw.attr('data-id');

    btn.addClass('loading');
    btn.children('i').removeClass().addClass('fa fa-spinner fa-spin');

    $.post('/blocks/homework/ajax/manage.php', {homeworkid: id, action: 'approve'}, function (res) {

        btn.removeClass('loading');
        btn.children('i').removeClass().addClass('fa fa-check');

        if (!res.success) {
            alert("Unable to approve.");
        }

        if (res.html) {
            $hw.replaceWith(res.html);
        }

    });
});


/**
 * Homework Notes
 */
$(document).on('click', '.editNotes', function (e) {
    e.preventDefault();

    var hw = $(this).closest('.homework');

    var notesP = hw.find('.notes');

    // Remember the text that was there first
    hw.data('originalNotes', notesP.html());

    notesP.find('a').each(function () {
        $(this).replaceWith($(this).attr('href'));
    });

    var text = notesP.text();
    var textarea = $('<textarea class="notes-input"></textarea>').val(text);

    $(notesP).html(textarea);
    $(notesP).show();

    $('.notes-input').autosize();

    hw.find('.editNotes').hide();
    hw.find('.saveNotes, .cancelNotes').show();

});

function closeHomeworkNoteEditing(hw, reset, text) {

    var textarea = hw.find('.notes-input');

    var notesP = hw.find('.notes');

    if (reset) {
        // Put the original text back
        notesP.html(hw.data('originalNotes'));
    } else {
        // Use the new text
        if (text) {
            notesP.html(text);
        } else {
            text = textarea.val();
            notesP.text(text);
            notesP.html(nl2br(notesP.html()));
        }
    }

    if (notesP.text().length < 1) {
        notesP.hide();
    }

    hw.find('.editNotes').show();
    hw.find('.saveNotes, .cancelNotes').hide();
}

$(document).on('click', '.cancelNotes', function (e) {
    e.preventDefault();
    var hw = $(this).closest('.homework');
    closeHomeworkNoteEditing(hw, true);
});

$(document).on('click', '.saveNotes', function (e) {
    e.preventDefault();

    var btn = $(this);
    if (btn.hasClass('loading')) {
        return false;
    }

    var hw = btn.closest('.homework');
    var id = hw.attr('data-id');

    btn.addClass('loading');
    btn.children('i').removeClass().addClass('fa fa-spinner fa-spin');

    var text = hw.find('.notes-input').val();

    $.post('/blocks/homework/ajax/notes.php', {homeworkid: id, action: 'save', notes: text}, function (res) {

        btn.removeClass('loading');
        btn.children('i').removeClass().addClass('fa fa-save');

        if (res.success) {
            closeHomeworkNoteEditing(hw, false, res.text);
        } else {
            alert("Unable to save notes.");
        }

    });

});

$(document).on('click', '.deleteHomeworkButton', function (e) {
    e.preventDefault();
    var btn = $(this);
    if (btn.hasClass('loading')) {
        return false;
    }

    var hw = btn.closest('.homework');
    var id = hw.attr('data-id');

    if (!confirm("Are you sure you want to delete this?")) {
        return false;
    }

    btn.addClass('loading');
    btn.children('i').removeClass().addClass('fa fa-spinner fa-spin');

    $.post('/blocks/homework/ajax/manage.php', {homeworkid: id, action: 'delete'}, function (res) {

        btn.removeClass('loading');
        btn.children('i').removeClass().addClass('fa fa-trash');

        if (res.success) {
            $('.homework[data-id=' + id + ']').slideUp();
        } else {
            alert("Unable to delete.");
        }
    });
});


/**
 * Add homework form
 */
$(document).on('click', '.addHomeworkPrivateToggle a', function (e) {
    e.preventDefault();
    $(this).closest('.addHomeworkPrivateToggle').find('a').removeClass('active');
    $(this).addClass('active');
    var value = $(this).attr('data-value');
    $(this).closest('form').find('input[name=private]').val(value);

    if (value == 1) {
        $('#groupid-select').append('<option value="0">Other / Not Applicable</option>');
    } else {
        $('#groupid-select').find('option[value="0"]').remove();
    }
});


function ensureFieldHasValue(field, errorText) {
    var value = field.val();
    if (value) {
        return true;
    }
    field.closest('.form-group').addClass('has-error');
    field.after('<p class="help-block error">' + errorText + '</p>');
    return false;
}

$(document).on('submit', '.addHomeworkForm', function (e) {

    $(this).find('.has-error').removeClass('has-error');
    $(this).find('.help-block.error').remove();
    var errors = false;

    if (!ensureFieldHasValue($(this).find('select[name=groupid]'), 'Please select a course.')) {
        errors = true;
    }

    if (!ensureFieldHasValue($(this).find('input[name=title]'), 'Please enter a title.')) {
        errors = true;
    }

    /*if (!ensureFieldHasValue($(this).find('textarea[name=description]'), 'Please enter a description.')) {
     errors = true;
     }*/

    if (!ensureFieldHasValue($(this).find('input[name=startdate]'), 'Please enter a start date.')) {
        errors = true;
    }

    if (!ensureFieldHasValue($(this).find('input[name=duedate]'), 'Please enter a due date.')) {
        errors = true;
    }

    var assigneddates = $(this).find('input[name=assigneddates]');
    if (!assigneddates.is(':hidden') && !ensureFieldHasValue(assigneddates, 'Please pick which days this homework is assigned for.')) {
        errors = true;
    }

    if (errors) {
        e.preventDefault();
        return false;
    }

});

Date.prototype.addDays = function (days) {
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
};

function getDateRange(startDate, stopDate) {
    var dateArray = [];
    var currentDate = startDate;
    while (currentDate <= stopDate) {
        dateArray.push(new Date(currentDate));
        currentDate = currentDate.addDays(1);
    }
    return dateArray;
}

function setPossibleDays() {

    var startDate = $('input[name=startdate]').val();
    var dueDate = $('input[name=duedate]').val();

    if (!startDate || !dueDate) {
        return false;
    }

    startDate = new Date(startDate);
    dueDate = new Date(dueDate);

    var possibleDates = getDateRange(startDate, dueDate);
    possibleDates.pop(); //Remove the last date as it's the date it needs to be turned in

    if (possibleDates.length < 2) {
        $('#assignedDatesGroup').slideUp();
        $('#assigneddates').val(formatDate('Y-m-d', startDate));
        return;
    } else {
        $('#assignedDatesGroup').slideDown();
    }

    var div = $('#possibleDays');
    var html = '';

    // Print Mon Tue Wed etc. across the top
    var days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    for (var day in days) {
        if (days.hasOwnProperty(day)) {
            html += '<li><span>' + days[day] + '</span></li>';
        }
    }

    for (var i = 0; i < possibleDates.length; i++) {
        var date = possibleDates[i];

        // If the first date is not a Monday add emptyness until it's the right day
        if (i === 0 && formatDate('N', date) !== '1') {
            for (var pad = 1; pad < parseInt(formatDate('N', date)); pad++) {
                html += '<li></li>';
            }
        }

        var selected = homeworkFormAssignedDates.indexOf(formatDate('Y-m-d', date)) != -1;

        html += '<li><a class="btn btn-block ' + (selected ? 'active btn-primary' : '') + '" data-date="' + formatDate('Y-m-d', date) + '">' + formatDate('M jS', date) + '</a></li>';
    }

    div.html(html);
    updateSelectedDates();
}

if (typeof homeworkFormAssignedDates === 'undefined') {
    var homeworkFormAssignedDates = [];
} else {
    $(document).ready(setPossibleDays);
}
function updateSelectedDates() {
    homeworkFormAssignedDates = [];
    $('#possibleDays').find('.btn.active').each(function () {
        homeworkFormAssignedDates.push($(this).attr('data-date'));
    });
    $('#assigneddates').val(homeworkFormAssignedDates.join(','));
}

$(document).on('click', '#possibleDays .btn', function () {
    $(this).toggleClass('active btn-primary');
    updateSelectedDates();
});

// Datepicker tweaks

// When you click on 'Today', select today
$.datepicker._gotoToday = function (id) {
    var target = $(id);
    var inst = this._getInst(target[0]);
    if (this._get(inst, 'gotoCurrent') && inst.currentDay) {
        inst.selectedDay = inst.currentDay;
        inst.drawMonth = inst.selectedMonth = inst.currentMonth;
        inst.drawYear = inst.selectedYear = inst.currentYear;
    }
    else {
        var date = new Date();
        inst.selectedDay = date.getDate();
        inst.drawMonth = inst.selectedMonth = date.getMonth();
        inst.drawYear = inst.selectedYear = date.getFullYear();
        // the below two lines are new
        this._setDateDatepicker(target, date);
        this._selectDate(id, this._getDateDatepicker(target));
    }
    this._notifyChange(inst);
    this._adjustDate(target);
};

// Add 'tomorrow' button to datepicker
$.datepicker._generateHTML_old = $.datepicker._generateHTML;
$.datepicker._generateHTML = function (inst) {
    var html = this._generateHTML_old(inst);

    // The button to add
    var tomorrowButton = '<button type="button" class="ui-datepicker-tomorrow ui-state-default ui-priority-secondary ui-corner-all">Tomorrow</button>';

    // Gonna put our button before this ...
    var doneButton = "<button type='button' class='ui-datepicker-close";

    var position = html.indexOf(doneButton);

    if (position !== -1) {
        // Add the button into the html
        html = [html.slice(0, position), tomorrowButton, html.slice(position)].join('');
    }

    return html;
};

$(document).on('click', '.ui-datepicker-tomorrow', function (e) {
    e.preventDefault();

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Which element is the datepicker open for?
    // http://stackoverflow.com/questions/16674019/how-to-tell-which-datepicker-the-widget-is-open-for
    var id = '#' + $.datepicker._curInst.id;
    var input = $(id);

    var year = tomorrow.getFullYear();
    var month = tomorrow.getMonth() + 1;
    var day = tomorrow.getDate();

    var tomorrowString = year + '-' + month + '-' + day;

    $(input).datepicker('setDate', tomorrowString);
    $(input).datepicker('hide');
    $(input).blur();
});


/*function datepickerAddTomorrowButtton(element) {

 console.log('ui', element);
 var widget = $(element).datepicker('widget');
 console.log('widget', widget);

 if (widget.find('.ui-datepicker-tomorrow').length < 1) {
 widget.find('.ui-datepicker-current').after('<button type="button" class="ui-datepicker-tomorrow ui-state-default ui-priority-secondary ui-corner-all">Tomorrow</button>');
 }
 }*/


// Student search
function studentSearch() {
    var q = $('.userList input').val();
    if (!q) {
        return;
    }
    var $userList = $(this).closest('.userList').find('.users');

    $userList.html('<div class="nothing"><i class="fa fa-spinner fa-spin"></i> Searching for <strong>' + q + '</strong>...</div>');

    $.get('ajax/studentsearch.php', {q: q}, function (res) {
        console.log('res', res);
        var html = '';

        if (res.users.length < 1) {
            html += '<div class="nothing"><i class="fa fa-frown-o"></i> Nothing to show here.</div>';
        } else {
            for (var id in res.users) {
                if (res.users.hasOwnProperty(id)) {
                    var user = res.users[id];
                    html += '<div class="col-sm-3"><a href="changeuser.php?userid=' + user.id + '" class="btn">';
                    html += user.firstname + ' ' + user.lastname;
                    html += '<span>' + user.idnumber + '</span>';
                    html += '</a></div>';
                    console.log(html);
                }
            }
        }
        $userList.html(html);
    });
}
$(document).ready(function () {
    if ($('.userList').length > 0) {
        $('.userList input[type=text]').bindWithDelay('keyup', studentSearch, 500);
    }
});
