<?php

class FinancialParams {

    /* Financial type */

    public const FINANCIAL_TYPE_CONTRACT = 1;
    public const FINANCIAL_TYPE_HISTORY = 2;
    public const FINANCIAL_TYPE_SEMINAR = 3;

    /* Funder type */

    public const FUNDER_TYPE_AMU = 1;
    public const FUNDER_TYPE_CNRS = 2;
    public const FUNDER_TYPE_MISC = 3;

    /* Expense type */
    
    public const EXPENSE_TYPE_STAFF = 1;
    public const EXPENSE_TYPE_INVESTMENT = 2;
    public const EXPENSE_TYPE_ADMINISTRATIVE = 3; 

    public function get_financial_type($typeid) {
        switch($typeid) {
            case FinancialParams::FINANCIAL_TYPE_CONTRACT: 
                return 'contract';
                break;
            case FinancialParams::FINANCIAL_TYPE_HISTORY: 
                return 'history';
                break;
            case FinancialParams::FINANCIAL_TYPE_SEMINAR:
                return 'seminar';
        }
    }
    public function save_financial($financial_type, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'lab_financial', array("financial_type"=>$financial_type, "object_id"=>$object_id, "eotp"=>$eotp, "funder_type"=>$funder_type, "expense_type"=>$expense_type, "expense_details"=>$expense_details, "amount"=>$amount ));
    }

    public function save_financial_contract($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_CONTRACT, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function save_financial_history($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_HISTORY, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function save_financial_seminar($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_SEMINAR, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial($object_id, $financial_type, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'lab_financial', array("eotp"=>$eotp, "funder_type"=>$funder_type, "expense_type"=>$expense_type, "expense_details"=>$expense_details, "amount"=>$amount ), array('object_id'=>$object_id, "financial_type"=>$financial_type));
    }

    public function modify_financial_contract($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial($object_id, FinancialParams::FINANCIAL_TYPE_CONTRACT, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial_history($id, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial($id, FinancialParams::FINANCIAL_TYPE_HISTORY, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial_seminar($id, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial($id, FinancialParams::FINANCIAL_TYPE_SEMINAR, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }
    
    public function delete_financial($object_id, $financial_type) {
        global $wpdb;
    $wpdb->delete($wpdb->prefix.'lab_financial', array("object_id"=>$object_id, 'financial_type'=>$financial_type));
    }

    public function delete_financial_contract($object_id) {
        $this->delete_financial($object_id, FinancialParams::FINANCIAL_TYPE_CONTRACT);
    }

    public function delete_financial_history($object_id) {
        $this->delete_financial($object_id, FinancialParams::FINANCIAL_TYPE_HISTORY);
    }

    public function delete_financial_seminar($object_id) {
        $this->delete_financial($object_id, FinancialParams::FINANCIAL_TYPE_SEMINAR);
    }
}


 

// TODO:  function to popuplate financial table




?>