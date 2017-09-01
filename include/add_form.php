<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays the form for adding homework.
 *
 * @var object $USER
 *
 * @package    block_homework_diary
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$mode = $hwblock->get_mode();

// Get all the user's classes.
$groups = $hwblock->groups->get_all_users_groups($USER->id);

$selectedgroupid = '';
if (isset($homeworkitem)) {
    $selectedgroupid = $homeworkitem->groupid;
} else if (!empty($_GET['groupid'])) {
    $selectedgroupid = $_GET['groupid'];
}

$private = 0;
?>

<form class="form form-horizontal addHomeworkForm" role="form" method="post">

    <?php
    if (FORMACTION == 'edit') {
        ?>
        <input type="hidden" name="editid" value="<?php echo $homeworkitem->id; ?>"/>
        <?php
    }
    ?>

    <input type="hidden" name="action" value="<?php echo(FORMACTION == 'edit' ? 'saveedit' : 'save'); ?>"/>

    <?php
    if ($mode == 'student') {
        if (FORMACTION == 'edit' && $homeworkitem->private) {
            $private = 1;
        }

        ?>
        <div class="form-group">
            <label for="shared" class="col-md-3 control-label">Add For:</label>

            <div class="col-md-9">
                <div class="row addHomeworkPrivateToggle">
                    <div class="col-md-6">
                        <a
                            class="btn btn-block <?php echo(!$private ? 'active' : ''); ?> publicHomeworkButton"
                            data-value="0"
                            href="#">
                            <i class="fa fa-group"></i>
                            <br/><b>Everybody in the Class</b>
                            <br/>(Everyone can see after teacher approves)
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a
                            class="btn btn-block  <?php echo($private ? 'active' : ''); ?> privateHomeworkButton"
                            data-value="1"
                            href="#">
                            <i class="fa fa-user"></i>
                            <br/><b>Just Me</b>
                            <br/>(Only you see it, no teacher approval needed)</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <input type="hidden" name="private" value="<?php echo $private; ?>"/>

    <div class="form-group">
        <label for="groupid-select" class="col-md-3 control-label">Class:</label>

        <div class="col-md-9">
            <select name="groupid" id="groupid-select">
                <option value="">Please select...</option>
                <?php
                foreach ($groups as $groupid => $group) {
                    echo '<option value="' . $group->id . '"' . ($group->id == $selectedgroupid ? 'selected' : '') . '>';
                    echo $group->coursefullname . ' - ' . $group->name;
                    echo '</option>';
                }
                if ($private) {
                    echo '<option value="0" ' . ($private && !$selectedgroupid ? 'selected' : '') . '>' .
                        'Other / Not Applicable</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3 control-label">Title:</label>

        <div class="col-md-9">
            <input type="text" id="title" name="title" placeholder="Title of the assignment"
                   value="<?php echo(FORMACTION == 'edit' ? $homeworkitem->title : ''); ?>"/>
        </div>
    </div>

    <div class="form-group">
        <label for="description" class="col-md-3 control-label">Description:</label>

        <div class="col-md-9">
        <textarea name="description" placeholder="What is the homework?"
                  rows="10"><?php echo(FORMACTION == 'edit' ? $homeworkitem->description : ''); ?></textarea>
        </div>
    </div>

    <?php
    if ($mode == 'student') {
        // Visible date hidden for students.
        $hidestartdate = true;
    } else {
        $hidestartdate = false;
    }
    ?>
    <div class="form-group" <?php echo($hidestartdate ? 'style="display:none;"' : ''); ?>>
        <label for="startdate" class="col-md-3 control-label">Visible From:</label>

        <div class="col-md-9">
            <input type="text" id="startdate" name="startdate"
                   value="<?php echo(FORMACTION == 'edit' ? $homeworkitem->startdate : date('Y-m-d')); ?>"/>

            <p class="help-block">(Students won't see this on their page until this date)</p>
            <script>
                $(function () {
                    $('#startdate').datepicker({
                        firstDay: 1,
                        minDate: 0,
                        maxDate: "+1Y",
                        showButtonPanel: true,
                        dateFormat: 'yy-mm-dd',
                        numberOfMonths: 2,
                        onSelect: setPossibleDays,
                        onClose: function (selectedDate) {
                            $('#duedate').datepicker("option", "minDate", selectedDate);
                        }
                    });
                    $(document).on('change', '#startdate', setPossibleDays);
                });
            </script>
        </div>
    </div>

    <div class="form-group">
        <label for="due" class="col-md-3 control-label">Due Date:</label>

        <div class="col-md-9">
            <input type="text" id="duedate" name="duedate"
                   placeholder="Enter a date the assignment should be handed in by. (YYYY-MM-DD)"
                   value="<?php echo(FORMACTION == 'edit' ? $homeworkitem->duedate : ''); ?>"/>
            <script>
                $(function () {
                    $('#duedate').datepicker({
                        firstDay: 1,
                        minDate: 0,
                        maxDate: "+1Y",
                        showButtonPanel: true,
                        dateFormat: 'yy-mm-dd',
                        numberOfMonths: 2,
                        onSelect: setPossibleDays
                    });
                    $(document).on('change', '#duedate', setPossibleDays);
                });
            </script>
        </div>
    </div>

    <div class="form-group" id="assignedDatesGroup" style="display:none;">
        <label for="assigned" class="col-md-3 control-label">Assigned Days:</label>

        <div class="col-md-9">
            <p class="help-block">Which days should students work on this task?</p>
            <input id="assigneddates" type="hidden" name="assigneddates" value=""/>
            <ul id="possibleDays" class="row"></ul>
        </div>
    </div>

    <?php
    if (FORMACTION == 'edit') {
        // Show the assigned day toggle buttons on pageload if editing and existing item.
        echo '<script> var homeworkFormAssignedDates = ' . json_encode($homeworkitem->get_assigned_dates()) . '; </script>';
    }
    ?>

    <div class="form-group">
        <label for="duration" class="col-md-3 control-label">Duration:</label>

        <div class="col-md-9">
            <input type="hidden" name="duration" value=""/>

            <div id="duration-slider"></div>
            <span class="help-block" id="duration-help"></span>

            <script>
                $(function () {
                    var durationLabels = {
                        0: '0 minutes',
                        5: '5 minutes',
                        10: '10 minutes',
                        15: '15 minutes',
                        20: '20 minutes',
                        25: '25 minutes',
                        30: '30 minutes',
                        35: '35 minutes',
                        40: '40 minutes',
                        45: '45 minutes',
                        50: '50 minutes',
                        55: '55 minutes',
                        60: '1 hour',
                        65: '1 hour 5 minutes',
                        70: '1 hour 10 minutes',
                        75: '1 hour 15 minutes',
                        80: '1 hour 20 minutes',
                        85: '1 hour 25 minutes',
                        90: '1 hour 30 minutes',
                        95: '1 hour 35 minutes',
                        100: '1 hour 40 minutes',
                        105: '1 hour 45 minutes',
                        110: '1 hour 50 minutes',
                        115: '1 hour 55 minutes',
                        120: '2 hours',
                        125: '2 hours 5 minutes',
                        130: '2 hours 10 minutes',
                        135: '2 hours 15 minutes',
                        140: '2 hours 20 minutes',
                        145: '2 hours 25 minutes',
                        150: '2 hours 30 minutes',
                        155: '2 hours 35 minutes',
                        160: '2 hours 40 minutes',
                        165: '2 hours 45 minutes',
                        170: '2 hours 50 minutes',
                        175: '2 hours 55 minutes',
                        180: '3 or more hours'
                    };

                    function setDuration(mins) {
                        //Set the label
                        var dur = durationLabels[mins];
                        if (mins < 180) {
                            dur = 'up to ' + dur;
                        }
                        $('#duration-help').html('(This task should take <strong>' + dur + '</strong> in total.)');
                        $('input[name=duration]').val(mins);
                    }

                    <?php
                    if (FORMACTION == 'edit') {
                        $initialminduration = $homeworkitem->duration;
                    } else {
                        $initialminduration = 30;
                    }
                    ?>

                    $('#duration-slider').slider({
                        min: 0,
                        step: 5,
                        max: 180,
                        values: [<?php echo $initialminduration; ?>],
                        slide: function (event, ui) {
                            setDuration(ui.values);
                        }
                    });

                    setDuration(<?php echo $initialminduration; ?>);
                });
            </script>
        </div>
    </div>

    <?php
    if ($mode == 'teacher' && FORMACTION == 'edit' && !$homeworkitem->approved) {
        $label = 'Save and Approve';
    } else if (FORMACTION == 'edit') {
        $label = 'Save Changes';
    } else {
        $label = 'Submit';
    }
    ?>

    <div class="form-group">
        <div class="col-md-offset-3 col-md-5">
            <button type="submit" class="btn btn-lg"><?php echo $label; ?></button>
        </div>
    </div>

</form>
