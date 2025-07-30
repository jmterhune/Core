$(function () {
    $('[data-toggle="tooltip"]').tooltip()


    crud.field('public_timeslot').onChange(function(field) {
        crud.field('lagtime').show(field.value == 1 || crud.field('scheduling').value ==1);
    }).change();

    crud.field('scheduling').onChange(function(field) {
        crud.field('lagtime').show(field.value == 1 || crud.field('public_timeslot').value ==1);
    }).change();

    crud.field('scheduling').onChange(function(field) {
        crud.field('maxlagtime').show(field.value == 1 || crud.field('public_timeslot').value ==1);
    }).change();

    crud.field('public_docket').onChange(function(field) {
        crud.field('public_docket_days').show(field.value == 1);
    }).change();

})
