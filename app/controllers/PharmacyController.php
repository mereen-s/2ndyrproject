<?php
// Dispensary queue, dispensing and cancelling.
class PharmacyController {
  public function queue(){ view('pharmacy', ['prescriptions'=>Prescription::pending()]); }
  public function cancel(){
    if (Prescription::cancel($_POST['prescription_id'])) flash('Prescription cancelled and removed (not yet dispensed).');
    else flash('Cannot cancel - this prescription has already been dispensed.', 'error');
    redirect('pharmacy');
  }
  public function dispense(){
    Prescription::dispense($_POST['prescription_id'], Auth::id(), $_POST['quantity']);
    flash('Dispensed and recorded.');
    redirect('pharmacy');
  }
}
