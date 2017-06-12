<script type="text/javascript">
    $(document).ready(function(){

        $(".sidebar_left").each(function(index,element){
            $(this).css('display', 'none');
        });

        var sidebar = {$sidebar};
        var num = $(".sidebar_left").length ;
        $(".sidebar_left").each(function(index,element){
            var href = $(this).find('a').attr('href');
            var flag = 0;
            var num = $(this).siblings().length ;  //不显示的个数
            $(this).parent().parent().css('display','none'); //先不让sub-menu显示

            $.each(sidebar, function(key,val){
                if( href.indexOf(val.name) > 3){
                    flag = 1;
                }
            });

            if(flag){
                $(this).css('display','inline');
                num = num - 1;
            }

            if(num != $(this).siblings().length ){
                //有li显示了 将sub-menu显示
                $(this).parent().parent().css('display','inline');
            }

        });

        
    });
</script>