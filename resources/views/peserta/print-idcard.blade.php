@include('documents.print-idcard', [
    'registration' => $registration,
    'appSettings' => $appSettings ?? []
])
