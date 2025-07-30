$(function () {

    crud.field('motion_id').onChange(function(field) {
        crud.field('custom_motion').show(field.value == 221);
    }).change();
})

