const router = require('./Components/backendRouter.js');

$('body').on('change', '#seasonIds', function () {
    changeUrl(document.getElementById("seasonIds").value);
});

/**
 * Method to change season id
 *
 * @param value
 */
function changeUrl(value) {
    let scheduleUrl = router.generate('team_schedule', {'id': value});

    document.location.href = scheduleUrl;
}