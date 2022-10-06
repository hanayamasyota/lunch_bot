<!-- CONTENTS -->
<div class="container dx-3 my-5 bg-lightnavy">
        <div>
            <p class="fw-bold text-center"><?php echo $shopName ?>のレビュー</p>
        </div>
        <form method="post" action="#">
            <table class="table border-navy border-navy">
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            Form1
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <input type="text" name="review1">
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            Form2
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <input type="text" name="review2">
                        </td>
                    </tr>
                    <tr>
                        <th class="col-5 py-4 bg-lightbrown">
                            Form3
                        </th>
                        <td class="col-7 py-4 bg-white">
                            <input type="text" name="review3">
                        </td>
                    </tr>
            </table>
            <div class="text-center">
                <button type="submit" class="btn-dark mb-3 text-center">
                    レビューを登録する
                </button>
            </div>
        </form>
    </div>