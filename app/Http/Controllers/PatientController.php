<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\StatusPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * get all data by Status patient
     *
     * @param int $idStatus
     * @return \Illuminate\Http\Response
     */
    public function getPatientByStatus($idStatus)
    {  
       return Patient::where('status_patient_id',$idStatus)->get();
    }

    /**
     * get id status patient
     *
     * @param string $statusName
     * @return \Illuminate\Http\Response
     */
    public function getIdStatusPatient($statusName)
    {
        $statusPatient = StatusPatient::where('status', $statusName)->first();
        return $statusPatient->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $patient = DB::table('patients')->join('status_patients','patients.id','=','status_patients.id')->select(['name','phone','status','alamat','in_date_at','out_date_at'])->get();
        
        $data = [
            'message' => 'The request succeeded',
            'data' => $patient,
        ];
        
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $this->validate($request,[
            'name' => 'required|string',
            'phone' => 'required|numeric',
            'alamat' => 'required|string',
            'status' => ['required', Rule::in(['treatment', 'death','recovered'])],
            'in_date_at' => 'required|date',
            'out_date_at' => 'date',
        ]);

        $patient = new Patient($request->except('status'));

        $statusId = $this->getIdStatusPatient($request->status);
        $statusPatient = StatusPatient::find($statusId);
        $statusPatient->patient()->save($patient);
        
        $data = [
            'message' => 'Data has create created'
        ];

        return response()->json($data, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $patient = Patient::find($id);

        if(!$patient) {
            $data = [
                'message' => 'Resource not found',
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Detail Patient',
            'data' => [
                'name' => $patient->name,
                'alamat' => $patient->alamat,
                'phone' => $patient->phone,
                'status' => $patient->statusPatient->status,
                'in_date_at' => $patient->in_date_at,
                'out_date_at' => $patient->out_date_at,
            ],
        ];

        return response()->json($data,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'name' => 'string',
            'phone' => 'numeric',
            'alamat' => 'string',
            'status' => [Rule::in(['treatment', 'death','recovered']),''],
            'in_date_at' => 'date',
            'out_date_at' => 'date',
        ]);

        $patient = Patient::find($id);
        if(!$patient) {
            $data = [
                'message' => 'Resource not found',
            ];
            return response()->json($data, 404);
        }
        
        $statusPatientId = $this->getIdStatusPatient($request->status);
        $statusPatient = StatusPatient::find($statusPatientId);
        $patient->update($request->except('status'));
        $statusPatient->patient()->save($patient);

        return response()->json(['message' => 'Resource is update successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);
        if(!$patient) {
            return response()->json(['message' => 'Resource not found'], 404);
        }
        $patient->delete();
        return response()->json(['message' => 'Resource is delete successfully'], 200);
    }

    /**
     * Search Resource by name
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        $patient = DB::table('patients')->where('name','like',"%".$name."%")->join('status_patients','patients.status_patient_id','=','status_patients.id')->get(['name','phone','status','alamat','in_date_at','out_date_at']);

        if (count($patient) == 0) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $response = [
            'message' => 'Get searched resource',
            'data' => $patient
        ];
        return response()->json($response, 200);
    }

    /**
     * Get data by status positive
     * 
     * @return \Illuminate\Http\Response
     */
    public function positive()
    {
        $patients = $this->getPatientByStatus(1);

        $data = [
            'message' => 'The request succeeded',
            'total' => count($patients),
            'data' => $patients,
        ];
        
        return response()->json($data, 200);
    }

    /**
     * Get data by status death
     * 
     * @return \Illuminate\Http\Response
     */
    public function dead()
    {
        $patients = $this->getPatientByStatus(2);

        $data = [
            'message' => 'The request succeeded',
            'total' => count($patients),
            'data' => $patients,
        ];
        
        return response()->json($data, 200);
    }

    /**
     * Get data by status recovered
     * 
     * @return \Illuminate\Http\Response
     */
    public function recovered()
    {
        $patients = $this->getPatientByStatus(3);

        $data = [
            'message' => 'The request succeeded',
            'total' => count($patients),
            'data' => $patients,
        ];
        
        return response()->json($data, 200);
    }

}
