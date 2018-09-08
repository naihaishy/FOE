function getEmailContent(){
        		//发送ajax请求
        		$.get("{:U('Header/showEmailUnreadContent')}",function(data,status){
        			
        			//console.log(data.length);
        			 //for循环遍历
        			
        			 for(var i=0;i < data.length;i++){
        			 	
        			 	
        			 		var id=data[i].id;
        			 		var sender=data[i].truename;
        			 		
        			 		
        			 		$li=$('<li></li>');
									
									$a=$('<a></a>');
									$a.attr("href","{:U('Email/getContent/id/id')}");
									
									$a_div=$('<div></div>');
									$a_div.attr('class','profile-photo');
									
									$a_div_img=$('<img>');
									$a_div_img.attr('src','__ADMIN__/SpaceLab/assets/img/avatar.gif');
									$a_div_img.attr('class','img-circle');
									
									$a_div2=$('<div></div>');
									$a_div2.attr('class','message-info');
									
									$a_div2_span=$('<sapn></sapn>');
									$a_div2_span.attr('class','sender');
									$a_div2_span.text($sender);
									
									$a_div2_span2=$('<sapn></sapn>');
									$a_div2_span2.attr('class','time');
									$a_div2_span2.text($sender);
									
									$a_div2_div=$('<div></div>');
									$a_div2_div.attr('class','message-content');
									$a_div2_div.text($sender);
									
									$a_div2.append($a_div2_span,$a_div2_span2,$a_div2_div);
									$a_div.append($a_div_img);
									$a.append($a_div,$a_div2);
									$li.append($a);
        			 		
        			 		$('#email-unread-content').append($li);
        			 }
							
							
							
        	 
									
								 
								
        			});
        		}	