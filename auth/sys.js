function cnfZip(){ return confirm('Do you want to download this folder as Zip?'); }
function cnfFile(){ return confirm('Do you want to download this file?'); }
function tglPnl(showId, pnlClass){ var cls = (pnlClass) ? pnlClass : 'tgl-pnl'; var matches = document.getElementsByClassName(cls); for(var i=0; i<matches.length; i++){ var ele = matches[i]; ele.style.display = 'none'; } document.getElementById(showId).style.display = 'block'; }
