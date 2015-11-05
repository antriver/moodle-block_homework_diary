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
 * @package    block_homework
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$mode = $hwblock->get_mode();

// Get all the user's classes.

/** @var object $USER */
$groups = $hwblock->groups->get_all_users_groups($USER->id);

$selectedcourseid = '';
$selectedgroupid = '';
if (isset($editiem)) {
    $selectedgroupid = $editiem->groupid;
} else if (!empty($_GET['groupid'])) {
    $selectedgroupid = $_GET['groupid'];
}

$private = 0;
?>

<form class="form form-horizontal addHomeworkForm" role="form" method="post">

<?php if (FORMACTION == 'edit') {
    ?>
    <input type="hidden" name="editid" value="<?php echo $editiem->id; ?>"/>
<?php
} ?>

<input type="hidden" name="action" value="<?php echo(FORMACTION == 'edit' ? 'saveedit' : 'save'); ?>"/>

<?php if ($mode == 'student') {

    if (FORMACTION == 'edit' && $editiem->private) {
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
} ?>
<input type="hidden" name="private" value="<?php echo $private; ?>"/>


<div class="form-group">
    <label for="groupIDSelelect" class="col-md-3 control-label">Class:</label>

    <div class="col-md-9">
        <select name="groupid" class="form-control" id="groupIDSelelect">
            <option value="">Please select...</option>
            <?php
            foreach ($groups as $groupid => $group) {
                // TODO: Ability to pass courseid in the URL and select the first group in the course.
                echo '<option
                    value="' . $group->id . '"
                    data-courseid="' . $group->courseid . '" ' . ($group->id == $selectedgroupid ? 'selected' : '') . '>';

                echo $group->coursefullname;

                echo ' - ' . $group->name;

                if ($group->id == $selectedgroupid) {
                    $selectedcourseid = $group->courseid;
                }
                echo '</option>';
            }
            if ($private) {
                echo '<option value="-1" ' . ($private && !$selectedgroupid ? 'selected' : '') . '>Other / Not Applicable</option>';
            }
            ?>
        </select>
        <input
            type="hidden"
            name="courseid"
            value="<?php echo(FORMACTION == 'edit' ? $editiem->courseid : $selectedcourseid); ?>"/>

    </div>
</div>

<div class="form-group">
    <label for="title" class="col-md-3 control-label">Title:</label>

    <div class="col-md-9">
        <input type="text" id="title" name="title" class="form-control" placeholder="Title of the assignment"
               value="<?php echo(FORMACTION == 'edit' ? $editiem->title : ''); ?>"/>
    </div>
</div>

<div class="form-group">
    <label for="description" class="col-md-3 control-label">Description:</label>

    <div class="col-md-9">
        <textarea name="description" class="form-control" placeholder="What is the homework?"
                  rows="10"><?php echo(FORMACTION == 'edit' ? $editiem->description : ''); ?></textarea>
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
        <input type="text" id="startdate" name="startdate" class="form-control"
               value="<?php echo(FORMACTION == 'edit' ? $editiem->startdate : date('Y-m-d')); ?>"/>

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
        <input type="text" id="duedate" name="duedate" class="form-control"
               placeholder="Enter a date the assignment should be handed in by. (YYYY-MM-DD)"
               value="<?php echo(FORMACTION == 'edit' ? $editiem->duedate : ''); ?>"/>
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

<?php if (FORMACTION == 'edit') {
    // Show the assigned day toggle buttons on pageload if editing and existing item.
    echo '<script> var homeworkFormAssignedDates = ' . json_encode($editiem->get_assigned_dates()) . '; </script>';
} ?>

<div class="form-group">
    <label for="duration" class="col-md-3 control-label">Duration:</label>

    <div class="col-md-9">
        <input type="hidden" name="duration" class="form-control" value=""/>

        <div id="duration-slider"></div>
        <span class="help-block" id="duration-help"></span>

        <script>
            $(function () {

                var durationLabels = {
                    0: '0 minutes',
                    15: '15 minutes',
                    30: '30 minutes',
                    45: '45 minutes',
                    60: '1 hour',
                    75: '1 hour 15 minutes',
                    90: '1 hour 30 minutes',
                    105: '1 hour 45 minutes',
                    120: '2 hours',
                    135: '2 hours 15 minutes',
                    150: '2 hours 30 minutes',
                    165: '2 hours 45 minutes',
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
                        $initialminduration = $editiem->duration;
                    } else {
                        $initialminduration = 30;
                    }
                ?>

                $('#duration-slider').slider({
                    min: 0,
                    step: 15,
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
if ($mode == 'teacher' && FORMACTION == 'edit' && !$editiem->approved) {
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
