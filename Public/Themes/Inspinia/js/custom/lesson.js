//上传处理
$(document).ready(function(){
            
 //视频上传处理 获取fid及信息 
Dropzone.options.myAwesomeDropzone = {
    url:"{:U('Course/File/upload',array('cid'=>session('course_id')))}",
    paramName: "file",
    autoProcessQueue: false,
    uploadMultiple: true,
    parallelUploads: 1,
    maxFiles: 1,
    maxFilesize:500,
    acceptedFiles:'video/mp4',
    addRemoveLinks:true,
    dictRemoveFile:'删除',

    // Dropzone settings
    init: function() {
        var myDropzone = this;
        /*var myDropzone = $("#my-awesome-dropzone").dropzone({
            
        });*/

        this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            myDropzone.processQueue();
        });
        this.on("sendingmultiple", function() {
        });
        this.on("successmultiple", function(files, response) {
        });
        this.on("errormultiple", function(files, response) {
        });
        this.on("success", function(file, serverResponse){
            var obj = eval ("(" + serverResponse + ")");
            var fid =  obj.fid.split(",")[0];
            $('#media_title').text(' ');
            $.post("{:U('Course/Manage/getOneMediaAjax')}",{fid:fid},function(data,status){
                //alert(data);
                if(data && status=='success'){
                    $('#media_title').text(data.title);
                    $("#media_id").val(fid);
                    $('#media_chose').hide();
                }                       
            })
            
        });
    }

}

//选择框 视频 fid
$('#media_idb').change(function(){
    var media_idb = $("#media_idb").find("option:selected").val();
    var media_idbtitle = $("#media_idb").find("option:selected").text();
    $('#media_title').text(media_idbtitle);
  $("#media_id").val(media_idb);
  $('#media_chose').hide();
  
});



//选择框 资料 fid
$('#file_id').change(function(){
    var file_id = $("#file_id").find("option:selected").val();
    var file_title = $("#file_id").find("option:selected").text();
    $('#material_title').text(file_title);
    $("#res_file_id").val(file_id);
});

});



//表单提交
$('#form-addc').on('click',function(){
    $('form').eq(1).submit();
});
  
$('#form-addles').on('click',function(){
  $('form').eq(2).submit();
});

$('#form-editles').on('click',function(){
  $('form').eq(3).submit();
});

$('#form-addres').on('click',function(){
  $('form').eq(4).submit();
});


//添加课时
function add_les(obj){
    var chapterid=$(obj).attr('chapterid');
    $("input[name='chapter_id']").val(chapterid);
    $('#createlesson').modal('show');
}

//删除课时
function del_les(obj){
    var lessonid=$(obj).attr('lessonid');
    var url="{:U('Manage/delLesson')}";
    $.post( 
        url,
        {lid:lessonid},
        function(data,status){
            if( data && status=='success'){
                alert('删除成功');
                window.location.reload();
            }else{
                alert('删除失败');
            }
        });
}

/*
 * 编辑课时 
 * ajax post获取该课时信息
 */
function edit_les(obj){
    var lessonid=$(obj).attr('lessonid');
    var lessontitle=$(obj).attr('lessontitle');
    $('#lesson-title').text(lessontitle);
    $('#lesson_id').val(lessonid);

    

    var url="{:U('Course/Manage/getOneLesson')}";
    $.post(
        url,{lid:lessonid},
        function(data,status){
            if( data && status=='success'){
                CKEDITOR.instances.text_content.setData( data.content);
                //初始化select option radio
                $("#exercise_id").find("option[value='" + data.exercise_id + "']").attr("selected", "selected");

                $(":radio[value='" + data.type + "']").attr("checked","checked");

                $('#media_title').text(data.media_title);
                $('#media_id').val(data.media_id);
                $('#media_title').after("<a href='javascript:;' onclick='edit_mediaid(this)'>编辑</a>");
                $('#media_chose').hide();
            }
        }
    );
    $('#editlesson').modal('show');
    
}
//关闭modal 清除数据
$('#editlesson').on('hide.bs.modal', function () {
       $('#media_title').siblings().remove();
});

//编辑媒体文件
function edit_mediaid(obj){
    $('#media_chose').show();
    //$('#media_title').text(' ');//清除之前文件信息 重新选择媒体文件
    $('#media_id').val('0');
} 

//添加课时资料
function add_res(obj){
    var lessonid = $(obj).attr('lessonid');
    $('#res_lesson_id').val(lessonid);
    var url = "{:U('Course/Manage/getOneMediaAjax')}";
    $.post(
        url,{lid:lessonid},
        function(data,status){
            if( data && status=='success'){
                $('#material_title').text(data.mtitle);
                $('#res_file_id').val(data.mid);
                
            }
        }
    );
    

    
    $('#addresource').modal('show');
} 

//关闭课时
function close_les(obj){
    var lessonid=$(obj).attr('lessonid');
    var url="{:U('Manage/closeLesson')}";
    $.post( 
        url,
        {lid:lessonid},
        function(data,status){
            if(data && status=='success'){
                alert('关闭成功');
                window.location.reload();
            }else{
                alert('关闭失败');
            }
        });
}

//发布课时
function pulish_les(obj){
    var lessonid=$(obj).attr('lessonid');
    var url="{:U('Manage/publishLesson')}";
    $.post( 
        url,
        {lid:lessonid},
        function(data,status){
            if(data && status=='success'){
                alert('发布成功');
                window.location.reload();
            }else{
                alert('发布失败');
            }
        });
}

//删除章节
function del_cha(obj){
    var chapterid=$(obj).attr('chapterid');
    var url="{:U('Manage/delChapter')}";
    $.post( 
        url,
        {chid:chapterid},
        function(data,status){
            if(data && status=='success'){
                alert('删除成功');
                window.location.reload();
            }else{
                alert('删除失败');
            }
        });
}

//类型选择 视频 图文 测试
$(document).ready(function(){
    $("#text_lesson").hide();
    $("#test_lesson").hide();  
    CKEDITOR.replace('text_content',{ height:'150px'  });
    hidentype();
});


$(":radio[name='type']").on('click',function(){
    hidentype();
});
//类型切花时隐藏部分区域
function hidentype(){
   var type=$("input[name='type']:checked").val();
     switch(type){
        case 'video':
            $("#video_lesson").show();
            $("#text_lesson").hide();  
            $("#test_lesson").hide();                   
        break;
        case 'text':
            $("#text_lesson").show();
            $("#video_lesson").hide();
            $("#test_lesson").hide();       
        break;
        case 'test':
            $("#test_lesson").show(); 
            $("#text_lesson").hide();
            $("#video_lesson").hide();      
        break;
        
     }
}
