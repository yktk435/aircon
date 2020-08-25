<!DOCTYPE html>
<html lang="ja" >
  <head>
    <meta charset="utf-8">
    <title>リモコン</title>
    <style type="text/css">
    .value{
      font-size: x-large;
    }
    .btn-real {
      display: inline-block;
      text-decoration: none;
      color: rgba(152, 152, 152, 0.43);/*アイコン色*/
      width: 150px;
      height: 150px;
      line-height: 150px;
      font-size: 80px;
      border-radius: 50%;
      text-align: center;
      overflow: hidden;
      font-weight: bold;
      background-image: linear-gradient(#e8e8e8 0%, #d6d6d6 100%);
      text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.66);
      box-shadow: inset 0 2px 0 rgba(255,255,255,0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
      border-bottom: solid 2px #b5b5b5;
      }
      .btn-real i {
      line-height: 80px;
      }
      .btn-real:active {
      /*押したとき*/
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.5), 0 2px 2px rgba(0, 0, 0, 0.19);
      border-bottom: none;
      }
    </style>
  </head>
  <body>
      <a href="#" class="btn-real on" >
        <i class="fa fa-power-off">on</i>
      </a>
      <a href="#" class="btn-real off" >
        <i class="fa fa-power-off">off</i>
      </a>
      <a href="#" class="btn-real up" >
        <i class="fa fa-power-off">up</i>
      </a>
      <a href="#" class="btn-real down" >
        <i class="fa fa-power-off">down</i>
      </a>
      <div class="value">
        
      </div>
      <script type="text/javascript">

      function postdata(data){
        let xhr = new XMLHttpRequest();
        xhr.open('POST', URL);
        xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
        xhr.send('&data=' + encodeURIComponent(data));
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
              console.log(xhr.responseText);
            }
          }

      }
      
      const URL = "http://192.168.3.2/aircon/php/exec.php";

      
      let adddata=document.querySelector('.value');
      let on=document.querySelector('.on');
      let off=document.querySelector('.off');
      let up=document.querySelector('.up');
      let down=document.querySelector('.down');
      let getvalue=document.querySelector('.btn-real');
      
      on.addEventListener('click',function(){
        postdata('on');
      });
      off.addEventListener('click',function(){
        postdata('off');
      });
      up.addEventListener('click',function(){
        postdata('up');
      });
      down.addEventListener('click',function(){
        postdata('down');
        
      });
      
      function getval(){
        let xhr = new XMLHttpRequest();
        //POST
        xhr.open('POST', URL);
        xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
        xhr.send('&data=' + encodeURIComponent("get"));

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {

                let res1 = xhr.responseText;
                document.querySelector('.value').innerHTML=res1;
                
            }
        }
      }

      window.onload=getval();


      </script>

  </body>
</html>
