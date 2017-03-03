<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Image Annotations</title>
    <link href="{{asset('static/css/bootstrap.min.css')}}" rel="stylesheet">
    <style type="text/css" media="all">
        @import "{{asset('static/css/annotation.css')}}";
    </style>
    <script type="text/javascript" src="{{asset('static/js/jquery.js')}}"></script>
    <script type="text/javascript" src="{{asset('static/js/jquery-ui.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('static/js/jquery.annotate.js')}}"></script>
    <script type="text/javascript" src="{{asset('static/js/bootstrap.min.js')}}"></script>
    <script language="javascript">
        var basePath = "http://localhost/LabelImagePhp/public/";
        filterId = 0;
        //offset changed, so temp solution.
        var outerTop = 0;
        var outerLeft = 0;
        var imageDbId = 0;
        var originNotes = null;
//        var myPicNames = new Array("images/pic1.jpg", "images/pic2.png", "images/pic3.png");
        //        var tmpNote1 = JSON.parse('[{"top":22,"left":22,"text":"a","groupId":0,"partId":0},{"top":22,"left":28,"text":"b","groupId":0,"partId":1},{"top":18,"left":22,"text":"c","groupId":1,"partId":0},{"top":67,"left":16,"text":"d","groupId":1,"partId":5}]');
        //        var tmpNote2 = JSON.parse('[{"top":82,"left":22,"text":"a","groupId":0,"partId":0},{"top":22,"left":68,"text":"b","groupId":0,"partId":1},{"top":58,"left":22,"text":"c","groupId":1,"partId":0},{"top":67,"left":76,"text":"d","groupId":1,"partId":1}]');
        //        var tmpNote3 = JSON.parse('[{"top":42,"left":22,"text":"a","groupId":0,"partId":0},{"top":52,"left":28,"text":"b","groupId":0,"partId":1},{"top":78,"left":22,"text":"c","groupId":1,"partId":0},{"top":67,"left":36,"text":"d","groupId":1,"partId":5}]');
        //        var myNotes = new Array(tmpNote1, tmpNote2, tmpNote3);
        //        var arrIdx = 0;
//        var readFileName = "D:/software/xampp/htdocs/caffe_rtpose/input_json/filenames.txt";

        initOuterTop_Left = function () {
            outerTop = $("#toAnnotate").offset().top;
            outerLeft = $("#toAnnotate").offset().left;
        }

        nextPerson = function () {
            //to show next person
            filterId = parseInt(filterId) + 1;
            //reload annotations
            var result = $("#toAnnotate").annotateImage.reload();
            if (!result) {
                $('#nextPerson_btn').prop('disabled', true);
                $('#ok_btn').prop('disabled', false);
            }
        };

        print = function () {
            var annotations = $.fn.annotateImage.getAnnotations();
            annotations = JSON.parse(annotations);
            return jsonRevWrapper(annotations);
//            console.log(jsonRevWrapper(annotations));
//            alert(JSON.stringify(jsonRevWrapper(annotations)));
        }

        do_finish_once = function (noteJson) {
            filterId = 0;
            $('#nextPerson_btn').prop('disabled', false);
            $('#ok_btn').prop('disabled', true);
            $.fn.annotateImage.removeCanvas();


            $("#toAnnotate").annotateImage({
                notes: noteJson,
                top: outerTop,
                left: outerLeft,
                filterId: filterId

            });
            console.log($.fn.annotateImage.getAnnotations());
        }



        nextPicture = function () {
            $.ajax({
                url: "http://localhost/LabelImagePhp/public/delData",
                type: "post",
                data: {
                    id: imageDbId,
                    data: print()
                }
            }).then(function() {
                $.getJSON( "http://localhost/LabelImagePhp/public/getData", function( data ) {
                    var imagePath = data['imagePath'];
                    console.log(data['noteJson']);
                    originNotes = data['noteJson'];
                    imageDbId = data['id'];
                    var noteJson = jsonWrapper(data['noteJson']);
//                var modifiedNoteJson = jsonWrapper(noteJson);
//                console.log(modifiedNoteJson);
                    console.log(noteJson);
                    nextPictureWithPara(imagePath, noteJson);
                });
            });
        }
        
        nextPictureWithPara = function (imagePath, noteJson) {
            var logo = document.getElementById('toAnnotate');
            $('#toAnnotate').attr("src", basePath + imagePath);
            logo.onload = function () {
                do_finish_once(noteJson);
            };
        }

        jsonWrapper = function (noteJson) {
            var jsonObj = JSON.parse(noteJson);
            var bodies = jsonObj['bodies'];
            var jsonArr = [];
            for (var idx in bodies) {
                var len = bodies[idx]['joints'].length;
//                var jsonArrOnePerson = [];
                for (var jdx = 0; jdx < len; jdx = jdx + 3) {
                    jsonArr.push({
                        left: bodies[idx]['joints'][jdx],
                        top: bodies[idx]['joints'][jdx + 1],
                        groupId: idx,
                        partId: jdx / 3,
                    });
                }
//                jsonArr.push(jsonArrOnePerson);
            }
            noteJson = jsonArr;
            return noteJson;
        }
        
        jsonRevWrapper = function (revNoteJson) {

            var res = clone(JSON.parse(originNotes));
            for (var idx in revNoteJson) {
                var top = revNoteJson[idx]['top'];
                var left = revNoteJson[idx]['left'];
                var groupId = revNoteJson[idx]['groupId'];
                var partId = revNoteJson[idx]['partId'];
                var top = revNoteJson[idx]['top'];
                res['bodies'][groupId]['joints'][partId * 3] = left;
                res['bodies'][groupId]['joints'][partId * 3 + 1] = top;
            }
            return res;
        }

        $(window).load(function () {
            initOuterTop_Left();
            nextPicture();
        });

        function clone(obj) {
            var copy;

            // Handle the 3 simple types, and null or undefined
            if (null == obj || "object" != typeof obj) return obj;

            // Handle Date
            if (obj instanceof Date) {
                copy = new Date();
                copy.setTime(obj.getTime());
                return copy;
            }

            // Handle Array
            if (obj instanceof Array) {
                copy = [];
                for (var i = 0, len = obj.length; i < len; i++) {
                    copy[i] = clone(obj[i]);
                }
                return copy;
            }

            // Handle Object
            if (obj instanceof Object) {
                copy = {};
                for (var attr in obj) {
                    if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
                }
                return copy;
            }

            throw new Error("Unable to copy obj! Its type isn't supported.");
        }

    </script>
</head>

<body>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <a class="navbar-brand" href="#">Label Image</a>
        <ul class="nav navbar-nav">
        </ul>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="jumbotron">
            <p>
                <br>
            </p>
            <img class="annotateMyClass" id="toAnnotate" src="#" alt="Trafalgar Square"/>
            <p></p>
            <p>
                <button class="btn btn-primary btn-sm" style="margin: 5px" href="#" role="button" id="nextPerson_btn"
                        onclick="nextPerson()">Next
                </button>
                <button class="btn btn-primary btn-sm" style="margin: 5px" href="#" role="button" id="ok_btn"
                        onclick="nextPicture()">Ok
                </button>
                <button class="btn btn-primary btn-sm" href="#" role="button" onclick="print()">Print</button>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4">
            <!--              <input type="file" name="file" id="file">
-->
        </div>
    </div>

</div>
<!--  <div>
    <div style="padding: 2px">
        <button id="nextPerson_btn" onclick="nextPerson()">Next</button>
    </div>

    <div style="padding: 2px">
        <button id="ok_btn" onclick="nextPicture()">OK</button>
    </div>

    <div>
        <button onclick="print()">Print</button>
    </div>
</div> -->
</body>
<script type="text/javascript">
    // $(":file").filestyle({buttonName: "btn-primary"});

//    document.getElementById('file').onchange = function () {
//        var file = this.files[0];
//        var reader = new FileReader();
//        reader.onload = function (progressEvent) {
//            // Entire file
//            console.log(this.result);
//            // By lines
//            var lines = this.result.split('\n');
//            for (var line = 0; line < lines.length; line++) {
//                console.log(lines[line]);
//            }
//        };
//        reader.readAsText(file);
//    };
</script>


</html>
