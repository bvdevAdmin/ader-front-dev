function pick() {
    const colorThief = new ColorThief();
    const color = colorThief.getColor($('#sampleImg')[0]);
    document.querySelector('#c1').style.backgroundColor = 'rgb(' + color + ')';
    var colors = colorThief.getPalette($('#sampleImg')[0], 10);
    for (var i = 0; i < colors.length; i++) {
        $("#c1").after($('<div style="display:inline-block; width:100px; height:100px; border-radius:50%;">').css("background-color", "rgb(" + colors[i] + ")"));
    }

const imgResize = (ratioX, ratioY, width) => {
    return (ratioY * width) / ratioX;
}    


