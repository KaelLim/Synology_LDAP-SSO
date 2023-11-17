var cursor = document.querySelector(".mymouse")
document.body.addEventListener("mousemove", function(e){
  cursor.style.left = e.clientX + "px";
  cursor.style.top = e.clientY + "px";
});

/*change mouse color*/

var cont = document.querySelector('.fr');
cont.addEventListener("mouseover", function(){
  cursor.setAttribute("style", "outline:white solid")
})
var cont = document.querySelector('.form-container');
cont.addEventListener("mouseout", function(){
  cursor.setAttribute("style", "outline:black solid")
});


