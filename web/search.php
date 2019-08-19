<?php
include("funcs.inc.php");

bab_header("Buildroot tests - Search page");
?>

<script type='text/javascript' src="search.js"></script>


<form action="index.php" method="get" onsubmit="submitForm()">
    <label>
        <span>Submitter:</span>
        <input type="text" name="submitter" placeholder="Submitter">
    </label>

    <label>
        <span>Failure reason:</span>
        <input type="text" name="reason"  placeholder="Reason">
    </label>

    <label>
        <span>Arch:</span>
        <input type="text" name="arch" placeholder="Architecture">
    </label>

    <span>
        <label>
            <span>Subarch:</span>
            <input type="text" name="subarch" placeholder="Sub-Architecture">
        </label>

        <label>
            <span>From:</span>
            <input type="date" name="date[from]" id="date_f" placeholder="From">
        </label>

        <fieldset>
            <legend>Static?</legend>
            <label>
                <input type="radio" name="static" value="1">
                <span>Y</span>
            </label>
            <label>
                <input type="radio" name="static" value="0">
                <span>N</span>
            </label>
        </fieldset>
    </span>

    <label>
        <span>Symbols:</span>
        <textarea name="symbols" id="symbols"></textarea>
    </label>

    <span>
        <label>
            <span>C library:</span>
            <input type="text" name="libc" placeholder="Library">
        </label>

        <label>
            <span>To:</span>
            <input type="date" name="date[to]" id="date_t" placeholder="To">
        </label>

        <fieldset>
            <legend>Status?</legend>
            <label>
                <input type="radio" name="status" value="OK">
                <span>OK</span>
            </label>
            <label>
                <input type="radio" name="status" value="NOK">
                <span>NOK</span>
            </label>
            <label>
                <input type="radio" name="status" value="TIMEOUT">
                <span>TIMEOUT</span>
            </label>
        </fieldset>
    </span>

    <input type="submit" value="Search!">
</form>

<?php
bab_footer();
?>
