<?php

namespace App\Rules;

use App\InventoryItem;
use App\TransactionItem;
use Illuminate\Contracts\Validation\Rule;

class InventoryCheckRule implements Rule
{
    private $param;
    private $item;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($this->param['transactionType'] != 'sales')return true;
        $itemList = $this->param['items'];
        $itemGroup = [];
        foreach($itemList as $item) {
            $previousQuantity = 0;
            if(!empty($item['id'])) {
                $previousTransaction = TransactionItem::find($item['id']);
                $previousQuantity = $previousTransaction['quantity'];
            }
            $itemGroup[$item['itemId']] = (($itemGroup[$item['itemId']] ?? 0) - $previousQuantity) + ($item['quantity'] ?? 0);
        }
        $noError = true;
        foreach($itemGroup as $itemId => $neededQuantity) {
            $this->item = InventoryItem::find($itemId);
            if($neededQuantity > $this->item->availableStock) {
                $noError = false;
                break;
            }
        }
        return $noError;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->item->itemName . ' does not have enough storage.';
    }
}
