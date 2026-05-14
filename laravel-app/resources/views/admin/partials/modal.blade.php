    <div class="modal fade" id="model-my-two-step-verify" data-bs-keyboard="false" tabindex="-1" aria-labelledby="scrollableLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scrollableLabel">Two Step Verify</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To perform this action, you need to complete 2-step verification to prevent unauthorized access.</p>

                    <input type="hidden" id="my-two-step-verify-btn">

                    <?php
                        if($global_user_response['response'][0]['2fa_status'] == "enable"){
                    ?>
                            <div class="form-group mt-2">
                                <label for="my-two-step-verify-code" class="form-label">Enter the 6-digit code from the authenticator app <span class="text-danger">*</span></label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control" id="my-two-step-verify-code" name="my-two-step-verify-code" placeholder="Enter code" required>
                                </div>
                            </div>
                    <?php
                        }else{
                    ?>
                            <div class="form-group mt-1">
                                <label for="my-two-step-verify-code" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="form-control-wrap">
                                    <input type="password" class="form-control" id="my-two-step-verify-code" name="my-two-step-verify-code" placeholder="Password" required>
                                </div>
                            </div>
                    <?php
                        }
                    ?>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="model-my-two-step-verify-btn" onclick="two_step_verify_tab_btn()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="model-my-action-confirmation" data-bs-keyboard="false" tabindex="-1" aria-labelledby="scrollableLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title model-my-action-confirmation-btn-title" id="scrollableLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you would like to do this?</p>

                    <input type="hidden" id="my-action-confirmation-btn">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="model-my-action-confirmation-btn" onclick="my_action_confirmation_btn()">Confirm</button>
                </div>
            </div>
        </div>
    </div>
