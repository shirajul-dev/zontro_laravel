            <footer class="footer footer-transparent d-print-none"
                style="max-width: 1200px; width: 100%; margin: auto; margin-top: 0px;">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item"><a href="{{ config('zontropay.help_url') }}" target="_blank"
                                        class="link-secondary" rel="noopener">Documentation</a></li>
                                <li class="list-inline-item"><a href="{{ config('zontropay.github_url') }}" target="_blank"
                                        class="link-secondary">Modules</a></li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    © {{ date('Y') }}
                                    <a href="{{ config('zontropay.app_url') }}" class="link-secondary" target="blank">{{ config('zontropay.app_name') }}</a>.
                                    All rights reserved.
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ config('zontropay.updates_url') }}" class="link-secondary"
                                        target="blank">{{
                                        config('zontropay.version') }} </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
