<div class="bte-mail" lang="{{ $locale }}">
    <style>
        .bte-mail{--bte-border:#d9dee7;--bte-bg:#fff;--bte-soft:#f6f7f9;--bte-text:#18202a;--bte-muted:#667085;--bte-primary:#1d4ed8;--bte-danger:#b42318;max-width:900px;color:var(--bte-text);font:14px/1.45 ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.bte-mail *{box-sizing:border-box}.bte-card{background:var(--bte-bg);border:1px solid var(--bte-border);border-radius:14px;overflow:hidden}.bte-head,.bte-section,.bte-actions{padding:20px}.bte-head{display:flex;gap:18px;justify-content:space-between;align-items:flex-start;border-bottom:1px solid var(--bte-border)}.bte-title{margin:0;font-size:21px}.bte-sub{margin:4px 0 0;color:var(--bte-muted)}.bte-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.bte-pill{padding:5px 9px;border-radius:999px;background:var(--bte-soft);font-size:12px}.bte-lang{min-width:130px}.bte-section{border-bottom:1px solid var(--bte-border)}.bte-section h3{margin:0 0 14px;font-size:16px}.bte-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.bte-grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}.bte-span-2{grid-column:span 2}.bte-field label{display:block;font-weight:600;margin-bottom:6px}.bte-input,.bte-select{width:100%;border:1px solid var(--bte-border);border-radius:9px;background:#fff;padding:10px 11px;color:var(--bte-text);outline:none}.bte-input:focus,.bte-select:focus{border-color:var(--bte-primary);box-shadow:0 0 0 3px rgba(29,78,216,.1)}.bte-help{margin:5px 0 0;color:var(--bte-muted);font-size:12px}.bte-error{margin:5px 0 0;color:var(--bte-danger);font-size:12px}.bte-switch{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid var(--bte-border);border-radius:10px;background:var(--bte-soft)}.bte-switch input{margin-top:3px}.bte-status{margin:16px 20px 0;padding:10px 12px;border-radius:9px;background:#ecfdf3;color:#067647}.bte-status.error{background:#fef3f2;color:var(--bte-danger)}.bte-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap}.bte-button{border:1px solid var(--bte-border);border-radius:9px;padding:10px 15px;background:#fff;color:var(--bte-text);font-weight:600;cursor:pointer}.bte-button.primary{background:var(--bte-primary);border-color:var(--bte-primary);color:#fff}.bte-button:disabled{opacity:.55;cursor:not-allowed}.bte-note{color:var(--bte-muted);font-size:12px;margin:12px 0 0}.bte-test{display:flex;gap:10px}.bte-test .bte-input{flex:1}@media(max-width:700px){.bte-head{display:block}.bte-lang{margin-top:14px;width:100%}.bte-grid,.bte-grid-3{grid-template-columns:1fr}.bte-span-2{grid-column:auto}.bte-actions{justify-content:stretch}.bte-button{flex:1}.bte-test{display:block}.bte-test .bte-button{width:100%;margin-top:10px}}
    </style>

    <div class="bte-card">
        <header class="bte-head">
            <div>
                <h2 class="bte-title">{{ __('nuewire-mail::mail.title', [], $locale) }}</h2>
                <p class="bte-sub">{{ __('nuewire-mail::mail.subtitle', [], $locale) }}</p>
                <div class="bte-meta">
                    <span class="bte-pill">{{ __('nuewire-mail::mail.active', ['driver' => strtoupper($activeDriver)], $locale) }}</span>
                    <span class="bte-pill">{{ __('nuewire-mail::mail.mailer', ['mailer' => $activeMailer], $locale) }}</span>
                </div>
            </div>
            <div class="bte-field bte-lang">
                <label for="bte-email-locale">{{ __('nuewire-mail::mail.language_label', [], $locale) }}</label>
                <select id="bte-email-locale" class="bte-select" wire:model.live="locale">
                    @foreach ($localeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </header>

        @if ($status !== '')
            <div class="bte-status {{ $statusType === 'error' ? 'error' : '' }}" role="status">{{ $status }}</div>
        @endif

        <form wire:submit="save">
            <section class="bte-section">
                <div class="bte-grid">
                    <div class="bte-field">
                        <label for="bte-email-driver">{{ __('nuewire-mail::mail.driver_label', [], $locale) }}</label>
                        <select id="bte-email-driver" class="bte-select" wire:model.live="driver">
                            @foreach ($driverOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('driver') <p class="bte-error">{{ $message }}</p> @enderror
                    </div>
                    <label class="bte-switch">
                        <input type="checkbox" wire:model="setAsDefault">
                        <span><strong>{{ __('nuewire-mail::mail.default_label', [], $locale) }}</strong><br><span class="bte-help">{{ __('nuewire-mail::mail.default_help', [], $locale) }}</span></span>
                    </label>
                </div>
            </section>

            <section class="bte-section">
                <h3>{{ __('nuewire-mail::mail.sender', [], $locale) }}</h3>
                <div class="bte-grid">
                    <div class="bte-field">
                        <label for="bte-from-address">{{ __('nuewire-mail::mail.from_address', [], $locale) }}</label>
                        <input id="bte-from-address" class="bte-input" type="email" wire:model="fromAddress" autocomplete="email">
                        @error('fromAddress') <p class="bte-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="bte-field">
                        <label for="bte-from-name">{{ __('nuewire-mail::mail.from_name', [], $locale) }}</label>
                        <input id="bte-from-name" class="bte-input" type="text" wire:model="fromName">
                        @error('fromName') <p class="bte-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="bte-field">
                        <label for="bte-reply-address">{{ __('nuewire-mail::mail.reply_to_address', [], $locale) }}</label>
                        <input id="bte-reply-address" class="bte-input" type="email" wire:model="replyToAddress" placeholder="{{ __('nuewire-mail::mail.optional', [], $locale) }}">
                        @error('replyToAddress') <p class="bte-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="bte-field">
                        <label for="bte-reply-name">{{ __('nuewire-mail::mail.reply_to_name', [], $locale) }}</label>
                        <input id="bte-reply-name" class="bte-input" type="text" wire:model="replyToName" placeholder="{{ __('nuewire-mail::mail.optional', [], $locale) }}">
                        @error('replyToName') <p class="bte-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bte-section">
                <h3>{{ $driverOptions[$driver] ?? strtoupper($driver) }}</h3>

                @if ($driver === 'smtp')
                    <div class="bte-grid bte-grid-3">
                        <div class="bte-field bte-span-2"><label>{{ __('nuewire-mail::mail.smtp.host', [], $locale) }}</label><input class="bte-input" wire:model="smtpHost">@error('smtpHost')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.smtp.port', [], $locale) }}</label><input class="bte-input" type="number" wire:model="smtpPort">@error('smtpPort')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.smtp.security', [], $locale) }}</label><select class="bte-select" wire:model="smtpScheme"><option value="smtp">{{ __('nuewire-mail::mail.smtp.smtp', [], $locale) }}</option><option value="smtps">{{ __('nuewire-mail::mail.smtp.smtps', [], $locale) }}</option></select>@error('smtpScheme')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.smtp.username', [], $locale) }}</label><input class="bte-input" wire:model="smtpUsername">@error('smtpUsername')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.smtp.password', [], $locale) }}</label><input class="bte-input" type="password" wire:model="smtpPassword" autocomplete="new-password">@if($hasSmtpPassword)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('smtpPassword')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.smtp.timeout', [], $locale) }}</label><input class="bte-input" type="number" wire:model="smtpTimeout">@error('smtpTimeout')<p class="bte-error">{{ $message }}</p>@enderror</div>
                    </div>
                @elseif ($driver === 'sendmail')
                    <div class="bte-field"><label>{{ __('nuewire-mail::mail.sendmail.path', [], $locale) }}</label><input class="bte-input" wire:model="sendmailPath">@error('sendmailPath')<p class="bte-error">{{ $message }}</p>@enderror</div>
                @elseif ($driver === 'ses')
                    <div class="bte-grid">
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.ses.key', [], $locale) }}</label><input class="bte-input" wire:model="sesKey">@error('sesKey')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.ses.secret', [], $locale) }}</label><input class="bte-input" type="password" wire:model="sesSecret">@if($hasSesSecret)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('sesSecret')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.ses.token', [], $locale) }}</label><input class="bte-input" type="password" wire:model="sesToken">@if($hasSesToken)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('sesToken')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.ses.region', [], $locale) }}</label><input class="bte-input" wire:model="sesRegion">@error('sesRegion')<p class="bte-error">{{ $message }}</p>@enderror</div>
                    </div><p class="bte-note">{{ __('nuewire-mail::mail.ses.iam_help', [], $locale) }}</p>
                @elseif ($driver === 'mailgun')
                    <div class="bte-grid">
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.mailgun.domain', [], $locale) }}</label><input class="bte-input" wire:model="mailgunDomain">@error('mailgunDomain')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.mailgun.secret', [], $locale) }}</label><input class="bte-input" type="password" wire:model="mailgunSecret">@if($hasMailgunSecret)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('mailgunSecret')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.mailgun.endpoint', [], $locale) }}</label><input class="bte-input" wire:model="mailgunEndpoint" placeholder="api.eu.mailgun.net">@error('mailgunEndpoint')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.mailgun.scheme', [], $locale) }}</label><select class="bte-select" wire:model="mailgunScheme"><option value="https">HTTPS</option><option value="http">HTTP</option></select>@error('mailgunScheme')<p class="bte-error">{{ $message }}</p>@enderror</div>
                    </div>
                @elseif ($driver === 'postmark')
                    <div class="bte-grid">
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.postmark.token', [], $locale) }}</label><input class="bte-input" type="password" wire:model="postmarkToken">@if($hasPostmarkToken)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('postmarkToken')<p class="bte-error">{{ $message }}</p>@enderror</div>
                        <div class="bte-field"><label>{{ __('nuewire-mail::mail.postmark.stream', [], $locale) }}</label><input class="bte-input" wire:model="postmarkMessageStreamId" placeholder="outbound">@error('postmarkMessageStreamId')<p class="bte-error">{{ $message }}</p>@enderror</div>
                    </div>
                @elseif ($driver === 'resend')
                    <div class="bte-field"><label>{{ __('nuewire-mail::mail.resend.key', [], $locale) }}</label><input class="bte-input" type="password" wire:model="resendKey" placeholder="re_...">@if($hasResendKey)<p class="bte-help">{{ __('nuewire-mail::mail.secret_saved', [], $locale) }}</p>@endif @error('resendKey')<p class="bte-error">{{ $message }}</p>@enderror</div>
                @elseif ($driver === 'log')
                    <div class="bte-field"><label>{{ __('nuewire-mail::mail.log.channel', [], $locale) }}</label><input class="bte-input" wire:model="logChannel" placeholder="stack"><p class="bte-help">{{ __('nuewire-mail::mail.log.help', [], $locale) }}</p>@error('logChannel')<p class="bte-error">{{ $message }}</p>@enderror</div>
                @elseif ($driver === 'array')
                    <p class="bte-help">{{ __('nuewire-mail::mail.array.help', [], $locale) }}</p>
                @endif
            </section>

            <section class="bte-section">
                <h3>{{ __('nuewire-mail::mail.test_heading', [], $locale) }}</h3>
                <div class="bte-test">
                    <input class="bte-input" type="email" wire:model="testRecipient" placeholder="email@example.com">
                    <button class="bte-button" type="button" wire:click="sendTest" wire:loading.attr="disabled" wire:target="sendTest">
                        <span wire:loading.remove wire:target="sendTest">{{ __('nuewire-mail::mail.send_test', [], $locale) }}</span>
                        <span wire:loading wire:target="sendTest">{{ __('nuewire-mail::mail.testing', [], $locale) }}</span>
                    </button>
                </div>
                @error('testRecipient') <p class="bte-error">{{ $message }}</p> @enderror
                <p class="bte-note">{{ __('nuewire-mail::mail.queue_note', [], $locale) }}</p>
            </section>

            <footer class="bte-actions">
                <button class="bte-button" type="button" wire:click="useLog" wire:loading.attr="disabled">{{ __('nuewire-mail::mail.use_log', [], $locale) }}</button>
                <button class="bte-button primary" type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('nuewire-mail::mail.save', [], $locale) }}</span>
                    <span wire:loading wire:target="save">{{ __('nuewire-mail::mail.saving', [], $locale) }}</span>
                </button>
            </footer>
        </form>
    </div>
</div>
