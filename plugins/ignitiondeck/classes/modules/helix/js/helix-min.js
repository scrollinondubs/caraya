jQuery(document).ready(function(){"use strict";function e(e,a){jQuery(window).height()-2*e<=a?jQuery(".helix-logo").fadeOut("fast"):jQuery(".helix-logo").fadeIn("fast")}var a=jQuery(".nav-icons li").size()-1,i=jQuery(".nav-icons li").eq(a-1).height()+2*jQuery(".nav-icons li").eq(a-1).css("padding-top").replace("px",""),n=jQuery(".nav-icons li").eq(a-1).position().top,o=parseInt(n)+parseInt(i),l=jQuery(".logged-out");e(i,o),jQuery(window).resize(function(l){n=jQuery(".nav-icons li").eq(a-1).position().top,o=parseInt(n)+parseInt(i),e(i,o)}),l.length<=0?jQuery(".idhelix").hover(function(){jQuery(".helix-logo > div").toggleClass("pop-out-content pop-out-content-visible")}):jQuery(".idhelix .helix-logo").click(function(){jQuery(".helix-logo > div").toggleClass("pop-out-content pop-out-content-visible")}),jQuery(".login-frame #helix_login_user").attr("placeholder","Your User Name"),jQuery(".login-frame #helix_login_pass").attr("placeholder","Your Password"),jQuery(".login-frame .login-password").after(jQuery(".login-frame .helix-error"));var r=jQuery(".dashboard-nav.left"),s=jQuery(".dashboard-nav.right"),t=r.find(".nav-icons"),d=r.find(".nav-content"),u=s.find(".nav-icons"),c=s.find(".nav-content"),h=jQuery(".dashboard-nav li a"),v=jQuery(".logged-out");if("undefined"!=typeof Storage){var y=Math.random();localStorage.setItem("helix_session",y),"open"==localStorage.helix_state&&jQuery(".dashboard-nav").removeClass("close-menu").addClass("open")}jQuery(".helixopen").click(function(e){e.preventDefault(),jQuery(".dashboard-nav").hasClass("active")?(localStorage.setItem("helix_state","closed"),Query(".dashboard-nav").removeClass("active").addClass("close-menu"),jQuery(".helix_avatar").addClass("active")):(jQuery(".dashboard-nav").removeClass("close-menu").addClass("active"),jQuery(".helix_avatar").removeClass("active open"))}),r.find(".close-list").on({click:function(e){r.addClass("close-menu").removeClass("active open"),localStorage.setItem("helix_state","closed"),jQuery(".helix_avatar").addClass("active")}}),s.find(".close-list").on({click:function(e){s.addClass("close-menu").removeClass("active open"),localStorage.setItem("helix_state","closed"),jQuery(".helix_avatar").addClass("active")}}),"0"==idf_logged_in&&(r.find(".close-list.login-frame").unbind("click"),s.find(".close-list.login-frame").unbind("click")),t.on({mouseenter:function(){v.length?jQuery(this).index()>2&&d.children().eq(jQuery(this).index()).addClass("active"):d.children().eq(jQuery(this).index()).addClass("active")},mouseleave:function(){d.children().eq(jQuery(this).index()).removeClass("active")}},"li"),u.on({mouseenter:function(){v.length?jQuery(this).index()>2&&c.children().eq(jQuery(this).index()).addClass("active"):c.children().eq(jQuery(this).index()).addClass("active")},mouseleave:function(){c.children().eq(jQuery(this).index()).removeClass("active")}},"li"),jQuery(h).click(function(e){"undefined"!==y&&localStorage.setItem("helix_state","open")}),jQuery('.login-frame [name="helix-wp-submit"]').click(function(e){var a=!1,i=!1,n=!1;return""===jQuery('.login-frame input[name="log"]').val()&&(a=!0,i=!0),""===jQuery('.login-frame input[name="pwd"]').val()&&(a=!0,n=!0),a&&(i||n)?(jQuery(".login-frame .login-password").after(jQuery(".login-frame .helix-error")),jQuery(".login-frame .helix-error.blank-field").show(),!1):!a&&(jQuery(".login-frame .helix-error").hide(),!0)})});