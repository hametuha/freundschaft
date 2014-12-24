/**!
 * Freundshaft JS
 */

/*global FreundschaftFollow: true*/

(function ($) {
    'use strict';

    // いろんなイベントを渡って使う変数を記録
    var nonce = '',
        loggedIn;

    // documentオブジェクトを監視
    $(document).on('rendered.freundschaft', function(){
        var $btns = $('.fs-disabled'), // disabledが未確認のボタンをこの時点で取得
            authorIds = [];
        // ログインしていないことをすでに確認済みだったら、
        // ログインボタンに変更
        if( false === loggedIn ){
            $btns.removeClass('fs-disabled').addClass('fs-login');
        }
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
                    // ログインチェックを記録
                    loggedIn = false;
                }
            }).fail(function(xhr, status, message){
                alert(message);
            });
        }
    });

    // DOMREADYでイベント発行
    $(window).ready(function(){
        $(document).trigger('rendered.freundschaft');
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

    // フォロワーをもっと読み込むボタン
    $(document).on('click', '.fs-more-btn', function(e){
        e.preventDefault();
        var $btn = $(this),
            endpoint = $btn.attr('href') + '&offset=' + $btn.attr('data-offset');
        // 読み込み中は何もしない
        if( !$btn.hasClass('loading') ){
            // 読み込み中に変更
            $btn.addClass('loading');
            $.get(endpoint).done(function(result){
                // HTMLを挿入
                $btn.before(result.html);
                // オフセットを更新
                $btn.attr('data-offset', result.offset);
                // ボタンの更新イベント
                $(document).trigger('rendered.freundschaft');
            }).fail(function(xhr, status, error){
                alert(error);
            }).always(function(){
                // 読み込み中を解除
                $btn.removeClass('loading');
            });
        }
    });

})(jQuery);
