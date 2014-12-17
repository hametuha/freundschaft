/**!
 * Freundshaft JS
 */

/*global FreundschaftFollow: true*/

(function ($) {
    'use strict';

    // ボタンの初期値
    var $btns, nonce = '';

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
            $.post(FreundschaftFollow.endpoint, {
                action: FreundschaftFollow.actions.fs_status,
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
                    // nonceを保存
                    nonce = result.nonce;
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
        var $btn = $(this),
            userId = $btn.attr('data-author-id');
        if( $btn.hasClass('fs-disabled') ){
            e.preventDefault();
        }else if( $btn.hasClass('fs-follow') ){
            // フォローする
            e.preventDefault();
            // 一時停止
            $btn.removeClass('fs-follow').addClass('fs-disabled');
            // Ajax
            $.post(FreundschaftFollow.endpoint, {
                action: FreundschaftFollow.actions.fs_follow,
                user_id: userId,
                _wpnonce: nonce
            }).done(function(result){
                if( result.success ){
                    // 成功
                    $btn.removeClass('fs-disabled').addClass('fs-following')
                }else{
                    // 失敗
                    $btn.removeClass('fs-disabled').addClass('fs-follow');
                    alert(result.message);
                }
            }).fail(function(xhr, status, error){
                $btn.removeClass('fs-disabled').addClass('fs-follow');
            });
        }else if( $btn.hasClass('fs-following') ){
            // フォロー解除
            e.preventDefault();
            // 一時停止
            $btn.removeClass('fs-following').addClass('fs-disabled');
            // Ajax
            $.post(FreundschaftFollow.endpoint, {
                action: FreundschaftFollow.actions.fs_unfollow,
                user_id: userId,
                _wpnonce: nonce
            }).done(function(result){
                if( result.success ){
                    // 成功
                    $btn.removeClass('fs-disabled').addClass('fs-follow')
                }else{
                    // 失敗
                    $btn.removeClass('fs-disabled').addClass('fs-following');
                    alert(result.message);
                }
            }).fail(function(xhr, status, error){
                $btn.removeClass('fs-disabled').addClass('fs-following');
            });
        }
    });

})(jQuery);
