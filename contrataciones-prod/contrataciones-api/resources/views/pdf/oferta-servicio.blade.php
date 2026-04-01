<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0.5cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8px; color: #000; margin: 0; padding: 0; }
        .container { width: 100%; border: 1px solid #000; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; }
        .bg-gray { background-color: #a6a6a6; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 7px; }
        .label { font-size: 6px; font-weight: bold; text-transform: uppercase; display: block; }
        .value { font-size: 8px; display: block; text-align: center; min-height: 10px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .no-border td { border: none; }

        /* Radio check simulation */
        .circle { width: 8px; height: 8px; border: 1px solid #000; border-radius: 50%; display: inline-block; vertical-align: middle; text-align: center; line-height: 8px; font-size: 6px; }
        .checked { background-color: #000; color: #fff; }

        .footer-note { font-size: 6px; padding: 4px; text-align: justify; border: 1px solid #000; border-top: none; }
        .signature-box { height: 35px; }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <table>
        <tr>
            <td colspan="2" style="width: 20%;" class="center">
                @php $logoPath = storage_path('app/public/logo-login.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height: 35px;">
                @else
                    <div style="font-size: 7px; color: #999;">[Logo]</div>
                @endif
            </td>
            <td colspan="6" class="center">
                <span class="bold" style="font-size: 10px;">OFERTA DE SERVICIO</span>
                <hr style="margin: 2px 0;">
                <span class="bold" style="font-size: 9px;">GERENCIA DE CONTRATACIONES</span>
            </td>
        </tr>

        <!-- DATOS PERSONALES -->
        <tr><td colspan="8" class="bg-gray">DATOS PERSONALES</td></tr>
        <tr>
            <td colspan="4"><span class="label">NOMBRES Y APELLIDOS</span><span class="value">{{ $empleado->nombre }}</span></td>
            <td colspan="2"><span class="label">C.I. Nº</span><span class="value">{{ $empleado->cedula }}</span></td>
            <td colspan="2" rowspan="6" class="center" style="width: 18%; padding: 0; vertical-align: top;">
                @if($empleado->foto)
                    <img src="{{ storage_path('app/public/' . $empleado->foto) }}" style="width: 100%; height: 200; display: block; object-fit: cover;">
                @else
                    <div style="border: 1px dashed #999; height: 160px; line-height: 160px; font-size: 7px; color: #666;">FOTO RECIENTE</div>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3"><span class="label">DIRECCIÓN DE HABITACIÓN</span><span class="value">{{ $empleado->direccion }}</span></td>
            <td colspan="2"><span class="label">CIUDAD</span><span class="value">{{ $empleado->ciudad }}</span></td>
            <td colspan="1"><span class="label">TELÉFONO</span><span class="value">{{ $empleado->telefono }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">LUGAR DE NACIMIENTO</span><span class="value">{{ $empleado->lugar_nacimiento }}</span></td>
            <td colspan="1"><span class="label">FECHA NAC.</span><span class="value">{{ $empleado->fecha_nacimiento }}</span></td>
            <td colspan="1"><span class="label">ESTADO CIVIL</span><span class="value">{{ $empleado->estado_civil }}</span></td>
            <td colspan="1"><span class="label">NACIONALIDAD</span><span class="value">{{ $empleado->nacionalidad }}</span></td>
            <td colspan="1"><span class="label">EDAD</span><span class="value">{{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->age : '' }}</span></td>
        </tr>
        <tr>
            <td colspan="1" class="center">
                <span class="label">SEXO</span>
                <span style="font-size: 5px;">FEM</span> <div class="circle {{ strtolower($empleado->sexo) == 'femenino' ? 'checked' : '' }}"></div>
                <span style="font-size: 5px;">MAS</span> <div class="circle {{ strtolower($empleado->sexo) == 'masculino' ? 'checked' : '' }}"></div>
            </td>
            <td colspan="1"><span class="label">ESTATURA</span><span class="value">{{ $empleado->estatura }}</span></td>
            <td colspan="1"><span class="label">PESO</span><span class="value">{{ $empleado->peso }}</span></td>
            <td colspan="1"><span class="label">TIPO SANGRE</span><span class="value">{{ $empleado->tipo_sangre }}</span></td>
            <td colspan="1"><span class="label">FECHA DISPONIBLE</span><span class="value">{{ $empleado->fecha_disponible }}</span></td>
            <td colspan="1" class="center">
                <span class="label">TIPO DE HABITACIÓN</span>
                <span style="font-size: 5px;">CASA</span> <div class="circle {{ strtolower($empleado->tipo_habitacion) == 'casa' ? 'checked' : '' }}"></div>
                <span style="font-size: 5px;">APTO</span> <div class="circle {{ strtolower($empleado->tipo_habitacion) == 'apartamento' ? 'checked' : '' }}"></div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="center">
                <span class="label" style="display: inline;">CARACTERÍSTICAS</span>
                <span style="font-size: 6px; margin-left: 10px;">PROPIA</span> <div class="circle {{ stripos($empleado->caracteristicas_habitacion, 'propia') !== false ? 'checked' : '' }}"></div>
                <span style="font-size: 6px; margin-left: 10px;">ALQUILADA</span> <div class="circle {{ stripos($empleado->caracteristicas_habitacion, 'alquila') !== false ? 'checked' : '' }}"></div>
            </td>
            <td colspan="3" style="padding: 0;">
                {{-- vacío para alinear con foto --}}
            </td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">CORREO ELECTRÓNICO</span><span class="value">{{ $empleado->email }}</span></td>
            <td colspan="1"><span class="label">COLEGIACIÓN Nº</span><span class="value">{{ $empleado->colegiacion_nro }}</span></td>
            <td colspan="3" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td style="border: none; border-right: 1px solid #000; width: 50%;">
                            <span class="label">LICENCIA DE CONDUCTOR</span>
                            <span class="label">Nº: <span style="font-weight: normal;">{{ $empleado->licencia_conductor_nro }}</span></span>
                            <span class="label">EXPIRACIÓN: <span style="font-weight: normal;">{{ $empleado->licencia_conductor_expiracion }}</span></span>
                        </td>
                        <td style="border: none; width: 50%; text-align: center;">
                            <span class="label center" style="display: block;">TALLAS</span>
                            <span style="font-size: 6px;">PANTALÓN: {{ $empleado->talla_pantalon }}</span><br>
                            <span style="font-size: 6px;">CAMISA: {{ $empleado->talla_camisa }}</span><br>
                            <span style="font-size: 6px;">ZAPATO: {{ $empleado->talla_zapato }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- DATOS FAMILIARES -->
        <tr><td colspan="8" class="bg-gray">DATOS FAMILIARES</td></tr>
        <tr class="center bold" style="font-size: 6px;">
            <td colspan="3">NOMBRES Y APELLIDOS</td>
            <td colspan="1">PARENTESCO</td>
            <td colspan="1">EDAD</td>
            <td colspan="1">NACIONALIDAD</td>
            <td colspan="2">NUMERO DE TELEFONO</td>
        </tr>
        @for($i = 0; $i < 4; $i++)
        @php $fam = $empleado->datosFamiliares->values()[$i] ?? null; @endphp
        <tr>
            <td colspan="3" class="center">{{ $fam->nombre ?? '' }}</td>
            <td colspan="1" class="center">{{ $fam->parentesco ?? '' }}</td>
            <td colspan="1" class="center">{{ $fam->edad ?? '' }}</td>
            <td colspan="1" class="center">{{ $fam->nacionalidad ?? '' }}</td>
            <td colspan="2" class="center">{{ $fam->telefono ?? '' }}</td>
        </tr>
        @endfor

        <!-- ESTUDIOS REALIZADOS -->
        <tr><td colspan="8" class="bg-gray">ESTUDIOS REALIZADOS</td></tr>
        <tr class="center bold" style="font-size: 6px;">
            <td colspan="1">NIVEL</td>
            <td colspan="2">INSTITUCIÓN</td>
            <td colspan="1">LUGAR</td>
            <td colspan="1">INICIO</td>
            <td colspan="1">CULMINACIÓN</td>
            <td colspan="2">GRADO / TÍTULO</td>
        </tr>
        @php $niveles = ['PRIMARIA', 'SECUNDARIA', 'PREGRADO', 'POSTGRADO', 'TÉCNICA']; @endphp
        @foreach($niveles as $nivel)
        @php
            $est = $empleado->estudios->filter(fn($e) => strtoupper($e->nivel) == $nivel || ($nivel == 'SECUNDARIA' && strtoupper($e->nivel) == 'BACHILLERATO'))->first();
        @endphp
        <tr>
            <td colspan="1" class="bold center" style="font-size: 6px;">{{ $nivel }}</td>
            <td colspan="2" class="center">{{ $est->institucion ?? '' }}</td>
            <td colspan="1" class="center">{{ $est->lugar ?? '' }}</td>
            <td colspan="1" class="center">{{ $est->fecha_inicio ?? '' }}</td>
            <td colspan="1" class="center">{{ $est->fecha_culminacion ?? '' }}</td>
            <td colspan="2" class="center">{{ $est->grado_titulo ?? '' }}</td>
        </tr>
        @endforeach

        <!-- ASISTENCIA DE CURSOS -->
        <tr><td colspan="8" class="bg-gray">ASISTENCIA DE CURSOS Y OTROS EVENTOS</td></tr>
        <tr class="center bold" style="font-size: 6px;">
            <td colspan="3">CURSO / EVENTO</td>
            <td colspan="2">INSTITUCIÓN</td>
            <td colspan="1">FECHA</td>
            <td colspan="1">HORAS</td>
            <td colspan="1">CERTIFICADO</td>
        </tr>
        @for($i = 0; $i < 3; $i++)
        @php $cur = $empleado->cursosEventos->values()[$i] ?? null; @endphp
        <tr>
            <td colspan="3" class="center">{{ $cur->nombre_curso ?? '' }}</td>
            <td colspan="2" class="center">{{ $cur->institucion ?? '' }}</td>
            <td colspan="1" class="center">{{ $cur->fecha ?? '' }}</td>
            <td colspan="1" class="center">{{ $cur->horas ?? '' }}</td>
            <td colspan="1" class="center">{{ $cur ? ($cur->certificado ? 'SÍ' : 'NO') : '' }}</td>
        </tr>
        @endfor

        <!-- IDIOMAS -->
        <tr>
            <td colspan="8" style="padding: 0; border: none;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr class="center bold" style="font-size: 6px;">
                        <td rowspan="2" style="width: 25%;">IDIOMA</td>
                        <td colspan="3" style="width: 25%;">HABLA</td>
                        <td colspan="3" style="width: 25%;">LEE</td>
                        <td colspan="3" style="width: 25%;">ESCRIBE</td>
                    </tr>
                    <tr class="center bold" style="font-size: 5px;">
                        <td>BIEN</td><td>REG</td><td>POCO</td>
                        <td>BIEN</td><td>REG</td><td>POCO</td>
                        <td>BIEN</td><td>REG</td><td>POCO</td>
                    </tr>
                    @foreach($empleado->idiomas as $idi)
                    <tr>
                        <td class="center">{{ $idi->idioma }}</td>
                        <td class="center">{{ (trim(strtolower($idi->habla)) == 'bien') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->habla)) == 'regular') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->habla)) == 'poco') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->lee)) == 'bien') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->lee)) == 'regular') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->lee)) == 'poco') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->escribe)) == 'bien') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->escribe)) == 'regular') ? 'X' : '' }}</td>
                        <td class="center">{{ (trim(strtolower($idi->escribe)) == 'poco') ? 'X' : '' }}</td>
                    </tr>
                    @endforeach
                    @if($empleado->idiomas->count() == 0)
                    <tr><td colspan="10" style="height: 12px;"></td></tr>
                    @endif
                </table>
            </td>
        </tr>

        <!-- HABILIDADES -->
        <tr><td colspan="8" class="bg-gray">HABILIDADES Y DESTREZAS ADICIONALES</td></tr>
        <tr><td colspan="8" style="height: 25px; text-align: justify;">{{ $empleado->habilidades_destrezas }}</td></tr>

        <!-- EXPERIENCIA LABORAL -->
        <tr><td colspan="8" class="bg-gray">EXPERIENCIA LABORAL</td></tr>
        @foreach($empleado->experienciasLaborales as $exp)
        <tr>
            <td colspan="5"><span class="label">NOMBRE DEL ORGANISMO O EMPRESA</span><span class="value">{{ $exp->empresa }}</span></td>
            <td colspan="3"><span class="label">DIRECCIÓN Y TELÉFONO</span><span class="value">{{ $exp->direccion_telefono }}</span></td>
        </tr>
        <tr>
            <td><span class="label">FECHA INGRESO</span><span class="value">{{ $exp->fecha_ingreso }}</span></td>
            <td><span class="label">FECHA RETIRO</span><span class="value">{{ $exp->fecha_retiro }}</span></td>
            <td><span class="label">SUELDO INICIAL</span><span class="value">{{ $exp->sueldo_inicial }}</span></td>
            <td><span class="label">SUELDO FINAL</span><span class="value">{{ $exp->sueldo_final }}</span></td>
            <td colspan="2"><span class="label">CARGO INICIAL</span><span class="value">{{ $exp->cargo_inicial }}</span></td>
            <td colspan="2"><span class="label">CARGO FINAL</span><span class="value">{{ $exp->cargo_final }}</span></td>
        </tr>
        <tr>
            <td colspan="4"><span class="label">NOMBRE DEL ÚLTIMO SUPERVISOR</span><span class="value">{{ $exp->nombre_supervisor }}</span></td>
            <td colspan="4"><span class="label">MOTIVO DE RETIRO</span><span class="value">{{ $exp->motivo_retiro }}</span></td>
        </tr>
        @endforeach

        <!-- REFERENCIAS PERSONALES -->
        <tr><td colspan="8" class="bg-gray">REFERENCIAS PERSONALES</td></tr>
        <tr class="center bold" style="font-size: 6px;">
            <td colspan="2">NOMBRE Y APELLIDO</td>
            <td colspan="2">PROFESIÓN</td>
            <td colspan="2">DIRECCIÓN</td>
            <td colspan="2">TELÉFONO</td>
        </tr>
        @for($i = 0; $i < 3; $i++)
        @php $ref = $empleado->referenciasPersonales->values()[$i] ?? null; @endphp
        <tr>
            <td colspan="2" class="center">{{ $ref->nombre ?? '' }}</td>
            <td colspan="2" class="center">{{ $ref->profesion ?? '' }}</td>
            <td colspan="2" class="center">{{ $ref->direccion ?? '' }}</td>
            <td colspan="2" class="center">{{ $ref->telefono ?? '' }}</td>
        </tr>
        @endfor
    </table>

    <!-- DECLARACION -->
    <div class="footer-note" style="font-style: italic; text-align: center;">
        DECLARO QUE LA INFORMACIÓN Y DATOS SUMINISTRADOS EN ESTA OFERTA DE SERVICIO SON VERDADEROS, EXACTOS Y AUTORIZO LA INVESTIGACIÓN DE LAS MISMAS. CONVENGO EN QUE SI SOY EMPLEADO, POSTERIORMENTE SI SE LLEGARA A COMPROBAR QUE HE INCURRIDO EN INEXACTITUD O FALSEDAD EN LOS DATOS SUMINISTRADOS, ELLO SE CONSIDERARÁ COMO CAUSA JUSTIFICADA PARA LA TERMINACIÓN DEL EMPLEO.
    </div>

    <table>
        <tr>
            <td colspan="3" class="bold center">LUGAR</td>
            <td colspan="2" class="bold center">FECHA</td>
            <td colspan="3" class="bold center">FIRMA</td>
        </tr>
        <tr class="signature-box">
            <td colspan="3" class="center">ANZOÁTEGUI</td>
            <td colspan="2" class="center">{{ date('d/m/Y') }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- REQUISITOS -->
    <table>
        <tr><td class="bg-gray">ANEXAR REQUISITOS OBLIGATORIOS</td></tr>
        <tr>
            <td style="padding: 2px 5px;">
                @if($empleado->tipo_empleado === 'Personal de Buque')
                    <ol style="margin: 2px; font-size: 7px;">
                        <li>Resumen Curricular (En PDF)</li>
                        <li>Cédula de Identidad (En PDF)</li>
                        <li>Certificado de suficiencia o Refrendo Legible (En PDF)</li>
                        <li>Pasaporte (En PDF)</li>
                        <li>Certificado Medico Maritimo (En PDF)</li>
                        <li>Cedula Marina con todos sus movimientos de embarque y desembarque (En PDF)</li>
                        <li>Foto Tipo Carnet (Fondo azul cubierta, Fondo rojo maquinas)</li>
                        <li>Certificados de Cursos OMI Legibles (En PDF)</li>
                        <li>Título de Bachiller (En PDF)</li>
                        <li>Título universitario (En PDF)</li>
                        <li>Constancias de Trabajos anteriores (En PDF)</li>
                        <li>Registro de Información Fiscal (RIF) legible (En PDF)</li>
                    </ol>
                    <div style="font-size: 7px; margin-top: 5px; font-weight: bold;">
                        OBSERVACIONES:<br>
                        <span style="font-weight: normal;">Todos los requisitos solicitados deben ser enviados Online al Correo: rrhh@avantebureau.com. Toda la documentación debe estar debidamente actualizada.</span>
                    </div>
                @else
                    <ol style="margin: 2px; font-size: 7px;">
                        <li>Resumen Curricular</li>
                        <li>Cédula de Identidad</li>
                        <li>Registro de Información Fiscal (RIF)</li>
                        <li>Foto Tipo Carnet</li>
                        <li>Referencias Personales</li>
                        <li>Título Profesional</li>
                        <li>Cursos y/o Certificados</li>
                        <li>Constancias de trabajos anteriores</li>
                        <li>Antecedentes Penales</li>
                        <li>Licencia de conducir</li>
                        <li>Partida de Nacimiento de hijos</li>
                        <li>Acta de Matrimonio</li>
                    </ol>
                @endif
            </td>
        </tr>
    </table>
</div>

<div style="font-size: 6px; text-align: right; margin-top: 5px;">
    Código: ABS-GM-FM-021/25 | Fecha de Vigencia: 03/02/2025
</div>

</body>
</html>
