<!DOCTYPE html>
<html>
<head>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <style>
        body { margin : 0 ; height : 1000vh }
        section.video { width : 100vw ; height : 100vh ; position : fixed }
        section.video video { position : absolute ; top : 0 ; left : 0 ; min-width : 100vw ; min-height : 100vh }
    </style>
</head>
<body>
    <section class="video"><video id="bg" src="https://d340a4zb19l6y1.cloudfront.net/24ss/main/ADER_B&O_01.mp4" muted></video></section>
<script>
$(document).ready(function() {
    $(window).scroll(function() {
        let duration = $("#bg").get(0).duration;
        console.log(duration);
        let p = $(this).scrollTop() / ($("body").height()-$(window).height());
        $("#bg")[0].currentTime = duration * p; 
    });
});
</script>
    </body>
</html>