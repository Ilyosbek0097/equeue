<?php

// Please add these two functions inside your controller file (e.g., SiteController.php)

/**
 * Fetches data for a single employee and returns it as JSON.
 * This is used to populate the fields in the "Edit Employee" modal.
 * @param int $id The employee record ID (from auth_assignment table)
 * @return array
 */
public function actionGetEmployee($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    // I am assuming 'Employee' is the model name for the `auth_assignment` table
    // based on your view file's comments.
    $model = \app\modules\equeue\models\Employee::findOne((int)$id);

    if ($model) {
        // Return the model's attributes as key-value pairs
        return ['success' => true, 'data' => $model->attributes];
    } else {
        return ['success' => false, 'message' => 'Xodim topilmadi.'];
    }
}

/**
 * Updates an existing employee's record based on the submitted form data.
 * @param int $id The employee record ID
 * @return \yii\web\Response
 */
public function actionUpdateEmployee($id)
{
    // I am assuming 'Employee' is the model name for the `auth_assignment` table
    $model = \app\modules\equeue\models\Employee::findOne((int)$id);

    if ($model->load(Yii::$app->request->post())) {
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Xodim ma\'lumotlari muvaffaqiyatli yangilandi.');
        } else {
            // It's good practice to log the actual validation errors
            // Yii::error($model->errors);
            Yii::$app->session->setFlash('error', 'Ma\'lumotlarni saqlashda xatolik yuz berdi.');
        }
    } else {
        Yii::$app->session->setFlash('error', 'Yuborilgan ma\'lumotlarda xatolik.');
    }

    // Redirect back to the employee list page after processing
    return $this->redirect(['employes']);
}
