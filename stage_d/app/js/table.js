'use strict';
// var ipc= require('electron').ipcMain

var closeButtons = document.querySelectorAll('.close');

for (var i = 0; i < closeButtons.length; i++) {
    var closeButton = closeButtons[i];
    preparecloseButton(closeButton);
}

function preparecloseButton(buttonEl) {

     buttonEl.addEventListener('click', function () {
      ipc.send('close-baumer-window');
});
}


// http://jsfiddle.net/DerekL/McJ4s/5/