/**!
 * Freundshaft JS
 */

/*global Freundschaft: true*/

(function ($) {
    'use strict';

    // ボタンの初期値
    var $btns;

    $(window).ready(function(){
        // DOM READYでボタンを取得
        $btns = $('.fs-btn');
        var authorIds = [];
        // ボタン全部の投稿者IDを取得して、ログイン判定
        $btns.each(function(index, btn){
            authorIds.push($(btn).attr('data-author-id'));
        });
        // 投稿者IDがあればAjax
        if( authorIds.length ){
            $.post(Freundschaft.endpoint, {
                action: Freundschaft.action,
                author_ids: authorIds
            }).done(function(result){
                if( result.logged_in ){
                    // ログインしているので、ユーザーに応じたボタンを出力
                    for( var prop in result.users ){
                        if( result.users.hasOwnProperty(prop) ){
                            var userId = prop.replace(/user_/, '');
                            if( result.users[prop] ){
                                // フォローしている
                                $('.fs-btn[data-author-id=' + userId + ']').removeClass('fs-disabled').addClass('fs-following');
                            }else{
                                // フォローしていない
                                $('.fs-btn[data-author-id=' + userId + ']').removeClass('fs-disabled').addClass('fs-follow');
                            }
                        }
                    }
                }else{
                    // ログインしていないので、
                    // ボタンをログインに
                    $btns.removeClass('fs-disabled').addClass('fs-login');
                }
            }).fail(function(xhr, status, message){
                alert(message);
            });
        }
    });

    $(document).on('click', '.fs-btn', function(e){
        if( $(this).hasClass('fs-disabled') ){
            e.preventDefault();
        }
    });

})(jQuery);
