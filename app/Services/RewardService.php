<?php

namespace App\Services;



class RewardService
{
    //商品给与积分
    public function give_reward($score = 1,$zone = 1 ,$num = 1,$price = 0)
    {
        switch ($zone){
            case 1:
                //绿色经济分区
                switch ($score){
                    //一倍积分
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                }
                break;
            case 2:
                //优选经济分区
                switch ($score){
                    //一倍积分
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                }
                break;
            case 3:
                //幸运值分区
                break;
            case 4:
                //卷区
                break;
        }
    }


}
