@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <x-card header="Notification Preferences">
                    <form action="{{ route('dashboard.settings.notification-preferences.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_notifications"
                                    {{ $preferences->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label">Receive email notifications</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">SMS Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sms_notifications"
                                    {{ $preferences->sms_notifications ? 'checked' : '' }}>
                                <label class="form-check-label">Receive SMS notifications</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">WhatsApp Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="whatsapp_notifications"
                                    {{ $preferences->whatsapp_notifications ? 'checked' : '' }}>
                                <label class="form-check-label">Receive WhatsApp notifications</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notification Frequency</label>
                            <select name="frequency" class="form-control" data-select2-selector="default">
                                <option value="immediate" {{ $preferences->frequency === 'immediate' ? 'selected' : '' }}>
                                    Immediate</option>
                                <option value="daily" {{ $preferences->frequency === 'daily' ? 'selected' : '' }}>Daily
                                </option>
                                <option value="weekly" {{ $preferences->frequency === 'weekly' ? 'selected' : '' }}>Weekly
                                </option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quiet Hours Start</label>
                                <input type="time" name="quiet_hours_start" class="form-control"
                                    value="{{ $preferences->quiet_hours_start }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quiet Hours End</label>
                                <input type="time" name="quiet_hours_end" class="form-control"
                                    value="{{ $preferences->quiet_hours_end }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
@endsection
