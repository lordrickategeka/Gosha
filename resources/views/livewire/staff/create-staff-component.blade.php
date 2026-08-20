<div>
    <x-layouts.dash-layout title="Staff create">
        <div class="max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">Create New Staff Member</h2>
                <form wire:submit.prevent="createStaff">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" wire:model="first_name" class="form-input">
                            @error('first_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" wire:model="last_name" class="form-input">
                            @error('last_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="display_name">Display Name</label>
                            <input type="text" id="display_name" wire:model="display_name" class="form-input">
                            @error('display_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" wire:model="email" class="form-input">
                            @error('email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" wire:model="phone_number" class="form-input">
                            @error('phone_number')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="staff_code">Staff Code</label>
                            <input type="text" id="staff_code" wire:model="staff_code" class="form-input">
                            @error('staff_code')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="national_id">National ID</label>
                            <input type="text" id="national_id" wire:model="national_id" class="form-input">
                            @error('national_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="employee_id_number">Employee ID Number</label>
                            <input type="text" id="employee_id_number" wire:model="employee_id_number"
                                class="form-input">
                            @error('employee_id_number')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" wire:model="date_of_birth" class="form-input">
                            @error('date_of_birth')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="gender">Gender</label>
                            <select id="gender" wire:model="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="employment_type">Employment Type</label>
                            <select id="employment_type" wire:model="employment_type" class="form-select">
                                <option value="">Select Employment Type</option>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contractor">Contractor</option>
                            </select>
                            @error('employment_type')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="job_title">Job Title</label>
                            <input type="text" id="job_title" wire:model="job_title" class="form-input">
                            @error('job_title')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="department">Department</label>
                            <select id="department" wire:model="department" class="form-select">
                                <option value="">Select Department</option>
                                <option value="workshop">Workshop</option>
                                <option value="front_desk">Front Desk</option>
                                <option value="accounts">Accounts</option>
                            </select>
                            @error('department')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="hired_at">Hired At</label>
                            <input type="date" id="hired_at" wire:model="hired_at" class="form-input">
                            @error('hired_at')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="terminated_at">Terminated At</label>
                            <input type="date" id="terminated_at" wire:model="terminated_at" class="form-input">
                            @error('terminated_at')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="termination_reason">Termination Reason</label>
                            <input type="text" id="termination_reason" wire:model="termination_reason"
                                class="form-input">
                            @error('termination_reason')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="probation_end_at">Probation End At</label>
                            <input type="date" id="probation_end_at" wire:model="probation_end_at"
                                class="form-input">
                            @error('probation_end_at')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="skill_level">Skill Level</label>
                            <select id="skill_level" wire:model="skill_level" class="form-select">
                                <option value="">Select Skill Level</option>
                                <option value="junior">Junior</option>
                                <option value="mid">Mid</option>
                                <option value="senior">Senior</option>
                            </select>
                            @error('skill_level')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="specializations">Specializations</label>
                            <input type="text" id="specializations" wire:model="specializations"
                                class="form-input">
                            @error('specializations')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="certifications">Certifications</label>
                            <input type="text" id="certifications" wire:model="certifications"
                                class="form-input">
                            @error('certifications')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="hourly_rate">Hourly Rate</label>
                            <input type="number" id="hourly_rate" wire:model="hourly_rate" class="form-input">
                            @error('hourly_rate')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Create Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </x-layouts.dash-layout>
</div>
