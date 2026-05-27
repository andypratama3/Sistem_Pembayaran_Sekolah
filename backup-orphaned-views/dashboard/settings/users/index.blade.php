@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card stretch stretch-full">
            <!-- Filter Form -->
            <div class="pt-4 pb-0 card-body">
                <form id="userFilterForm" class="row g-3">
                    @csrf
                    <h5>Filter Users</h5>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-uppercase">Cari</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama atau email...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase">Role</label>
                        <select name="role" class="form-control" data-select2-selector="default">
                            <option value="">Semua Role</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Guru</option>
                            <option value="student">Siswa</option>
                            <option value="parent">Orang Tua</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase">Status</label>
                        <select name="status" class="form-control" data-select2-selector="status">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-items-end d-flex">
                        <button type="button" id="applyUserFilter" class="btn btn-primary">
                            <i class="feather-filter"></i> Filter
                        </button>
                        <button type="button" id="resetUserFilter" class="btn btn-outline-secondary ms-1">
                            <i class="feather-refresh-cw"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="userList">
                        <thead>
                            <tr>
                                <th class="wd-30">
                                    <div class="mb-1 btn-group">
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAllUsers">
                                            <label class="custom-control-label" for="checkAllUsers"></label>
                                        </div>
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="single-item">
                                <td>
                                    <div class="item-checkbox ms-1">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input checkbox" id="checkBox_1">
                                            <label class="custom-control-label" for="checkBox_1"></label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="gap-3 hstack">
                                        <div class="avatar-image avatar-md">
                                            <img src="{{ asset('assets/images/avatar/1.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <div>
                                            <span class="text-truncate-1-line">John Doe</span>
                                            <small class="fs-12 fw-normal text-muted">User ID: 001</small>
                                        </div>
                                    </a>
                                </td>
                                <td><a href="javascript:void(0)">john@example.com</a></td>
                                <td><span class="badge bg-soft-primary">Admin</span></td>
                                <td><span class="badge bg-soft-success">Active</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <div class="gap-2 hstack justify-content-end">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md">
                                            <i class="feather-edit-3"></i>
                                        </a>
                                        <div class="dropdown">
                                            <a href="javascript:void(0)" class="avatar-text avatar-md"
                                                data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                <i class="feather-more-horizontal"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                        <i class="feather-eye me-3"></i>
                                                        <span>View</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                        <i class="feather-trash-2 me-3"></i>
                                                        <span>Delete</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_2">
                                <label class="custom-control-label" for="checkBox_2"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#987456</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="text-white avatar-image avatar-md bg-warning">N</div>
                            <div>
                                <span class="text-truncate-1-line">Nancy Elliot</span>
                                <small class="fs-12 fw-normal text-muted">nancy.elliot@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$120.50 USD</td>
                    <td>2023-05-20, 12:23PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-warning text-warning">Unpaid</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_3">
                                <label class="custom-control-label" for="checkBox_3"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#741258</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="avatar-image avatar-md">
                                <img src="{{ asset('assets/images/avatar/2.png') }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <span class="text-truncate-1-line">Green Cute</span>
                                <small class="fs-12 fw-normal text-muted">green.cute@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$300.00 USD</td>
                    <td>2023-01-02, 10:36AM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_4">
                                <label class="custom-control-label" for="checkBox_4"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#321456</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="text-white avatar-image avatar-md bg-teal">H</div>
                            <div>
                                <span class="text-truncate-1-line">Henry Leach</span>
                                <small class="fs-12 fw-normal text-muted">henry.leach@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$249.99 USD</td>
                    <td>2023-04-25, 04:22PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_5">
                                <label class="custom-control-label" for="checkBox_5"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#357895</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="avatar-image avatar-md">
                                <img src="{{ asset('assets/images/avatar/3.png') }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <span class="text-truncate-1-line">Marianne Audrey</span>
                                <small class="fs-12 fw-normal text-muted">marine.adrey@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$150.00 USD</td>
                    <td>2023-02-15, 05:23PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_6">
                                <label class="custom-control-label" for="checkBox_6"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#321456</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="avatar-image avatar-md">
                                <img src="{{ asset('assets/images/avatar/1.png') }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <span class="text-truncate-1-line">Alexandra Della</span>
                                <small class="fs-12 fw-normal text-muted">alex@example.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$249.99 USD</td>
                    <td>2023-04-25, 11:43AM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_7">
                                <label class="custom-control-label" for="checkBox_7"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#987456</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="text-white avatar-image avatar-md bg-warning">N</div>
                            <div>
                                <span class="text-truncate-1-line">Nancy Elliot</span>
                                <small class="fs-12 fw-normal text-muted">nancy.elliot@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$120.50 USD</td>
                    <td>2023-05-20, 03:46PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-warning text-warning">warning</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_8">
                                <label class="custom-control-label" for="checkBox_8"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#741258</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="avatar-image avatar-md">
                                <img src="{{ asset('assets/images/avatar/2.png') }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <span class="text-truncate-1-line">Green Cute</span>
                                <small class="fs-12 fw-normal text-muted">green.cute@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$300.00 USD</td>
                    <td>2023-01-02, 02:35PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_9">
                                <label class="custom-control-label" for="checkBox_9"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#321456</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="text-white avatar-image avatar-md bg-teal">H</div>
                            <div>
                                <span class="text-truncate-1-line">Henry Leach</span>
                                <small class="fs-12 fw-normal text-muted">henry.leach@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$249.99 USD</td>
                    <td>2023-04-25,06:35PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-danger text-danger">Declined</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="single-item">
                    <td>
                        <div class="item-checkbox ms-1">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox" id="checkBox_10">
                                <label class="custom-control-label" for="checkBox_10"></label>
                            </div>
                        </div>
                    </td>
                    <td><a href="invoice-view.html" class="fw-bold">#357895</a></td>
                    <td>
                        <a href="javascript:void(0)" class="gap-3 hstack">
                            <div class="avatar-image avatar-md">
                                <img src="{{ asset('assets/images/avatar/3.png') }}" alt="" class="img-fluid">
                            </div>
                            <div>
                                <span class="text-truncate-1-line">Marianne Audrey</span>
                                <small class="fs-12 fw-normal text-muted">marianne.audrey@outlook.com</small>
                            </div>
                        </a>
                    </td>
                    <td class="fw-bold text-dark">$150.00 USD</td>
                    <td>2023-02-15, 08:36PM</td>
                    <td><a href="javascript:void(0);">#SDEG4589SE1E</a></td>
                    <td>
                        <div class="badge bg-soft-success text-success">Complted</div>
                    </td>
                    <td>
                        <div class="gap-2 hstack justify-content-end">
                            <a href="invoice-view.html" class="avatar-text avatar-md">
                                <i class="feather feather-eye"></i>
                            </a>
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                                    data-bs-offset="0,21">
                                    <i class="feather feather-more-horizontal"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-edit-3 me-3"></i>
                                            <span>Edit</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printBTN" href="javascript:void(0)">
                                            <i class="feather feather-printer me-3"></i>
                                            <span>Print</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-clock me-3"></i>
                                            <span>Remind</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-archive me-3"></i>
                                            <span>Archive</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-alert-octagon me-3"></i>
                                            <span>Report Spam</span>
                                        </a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)">
                                            <i class="feather feather-trash-2 me-3"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const baseUrl = '{{ route('dashboard.settings.users.index') }}';

            function buildUserFilterUrl() {
                const filters = {};
                $('#userFilterForm').serializeArray().forEach(function(item) {
                    if (item.value && item.value.trim() !== '') {
                        filters[item.name] = item.value.trim();
                    }
                });

                const queryString = $.param(filters);
                return queryString ? baseUrl + '?' + queryString : baseUrl;
            }

            $('#applyUserFilter').on('click', function() {
                try {
                    const url = buildUserFilterUrl();
                    window.location.href = url;
                } catch (error) {
                    console.error('Filter error:', error);
                    Swal.fire('Error', 'Failed to apply filter. Please try again.', 'error');
                }
            });

            $('#resetUserFilter').on('click', function() {
                try {
                    $('#userFilterForm')[0].reset();
                    $('[data-select2-selector]').val(null).trigger('change');
                    window.location.href = baseUrl;
                } catch (error) {
                    console.error('Reset filter error:', error);
                }
            });

            $('#userFilterForm input[type="text"]').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#applyUserFilter').trigger('click');
                }
            });
        });
    </script>
@endpush
