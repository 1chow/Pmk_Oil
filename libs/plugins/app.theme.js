$(document).ready(function(){

// SCROLL
$(".sidebar-nicescroller").niceScroll({
    cursorcolor: '#57606f',
    cursorborder: '0px solid #fff',
});
$(".sidebar-nicescroller").getNiceScroll().resize();
$(".sidebarRight-nicescroller").niceScroll({
    cursorcolor: '#57606f',
    cursorborder: '0px solid #fff',
});
$(".sidebarRight-nicescroller").getNiceScroll().resize();

$('.scrollsmall').slimscroll({
    size: '5px'
}).parent().css({
});

/** TOGGLE **/
$(".btn-collapse-sidebar-left").click(function(){
    $(".top-navbar").toggleClass("toggle");
    $(".sideLeft").toggleClass("toggle");
    $(".pageContent").toggleClass("toggle");
    $(".footer").toggleClass("toggle");
    $(".navbar-toolbar").toggleClass("toggle");
    $(".navbar-right").toggleClass("toggle");
    $(".icon-dinamic").toggleClass("rotate-180");
});
$(".btn-collapse-sidebar-right").click(function(){
    $(".top-navbar").toggleClass("toggle-left");
    $(".sideLeft").toggleClass("toggle-left");
    $(".sideRight").toggleClass("toggle-left");
    $(".top-navRight").toggleClass("toggle-left");
    $(".footer").toggleClass("toggle-left");
    $(".pageContent").toggleClass("toggle-left");
    $(".navbar-left").toggleClass("toggle-left");
});

// MENU NAV
$('#accordion').dcAccordion({
    eventType: 'click',
    autoClose: true,
    saveState: true,
    disableLink: true,
    speed: 'slow',
    showCount: false,
    autoExpand: true,
    classExpand: 'dcjq-current-parent'
});

$('[data-toggle="tooltip"]').tooltip();
$('[data-toggle="popover"]').popover();

// iCheck
$('input').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
});

});

