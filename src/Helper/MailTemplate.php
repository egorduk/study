<?php

namespace Helper;


class MailTemplate
{
    public static function getCreateCancelRequest($order, $param) {
        return
        '<p>Пользователь '. $order->getUser()->getLogin() . ' хочет отменить заказ:</p>' .
        '<p>' . $order->getTheme() . '</p>' .
        '<p>Комментарий: ' . $param['comment'] . '</p>' .
        '<p>Процент: ' . $param['textPercent'] . '</p>';
    }

    public static function getRemoveCancelRequest($order, $param) {
        return
            '<p>Пользователь '. $order->getUser()->getLogin() . ' отозвал заявку в арбитраж по заказу:</p>' .
            '<p>' . $order->getTheme() . '</p>';
    }

}