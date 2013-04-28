function setTheme(elementname,name)
{
    var element = $(elementname);
    var currentTheme = element.attr('data-theme');
    element.removeClass('ui-body-' + currentTheme);
    element.addClass('ui-body-' + name);
    element.trigger('refresh');
}
